<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Koleksi;      
use App\Models\Peminjaman;   
use App\Models\User;
use App\Models\PerpustakaanEksemplar;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;

class PerpustakaanController extends Controller
{
    public function dashboard(): View
    {
        $totalJudul = Koleksi::count();
        $totalEksemplar = Koleksi::sum('jumlah_stok'); 
        $peminjamanAktif = Peminjaman::where('status', 'Dipinjam')->count();

        $peminjamanTerlambat = Peminjaman::with(['koleksi', 'user'])
            ->where('status', 'Dipinjam')
            ->whereDate('jatuh_tempo', '<', Carbon::today())
            ->get();
        $terlambatCount = $peminjamanTerlambat->count();

        $aktivitasTerakhir = Peminjaman::with(['user', 'koleksi'])
            ->latest()
            ->take(5)
            ->get();

        $koleksiPopuler = Koleksi::withCount('peminjamans')
            ->orderByDesc('peminjamans_count')
            ->take(5)
            ->get();

        return view('perpustakaan.dashboard', compact(
            'totalJudul',
            'totalEksemplar',
            'peminjamanAktif',
            'terlambatCount',
            'peminjamanTerlambat',
            'aktivitasTerakhir',
            'koleksiPopuler'
        ));
    }

    public function indexPeminjaman()
    {
        $peminjamans = Peminjaman::with(['user.mahasiswa', 'user.dosen', 'koleksi', 'eksemplar'])
            ->where('status', 'Dipinjam')
            ->orderBy('jatuh_tempo', 'asc')
            ->paginate(15);
            
        return view('perpustakaan.peminjaman.index', compact('peminjamans'));
    }

    public function createPeminjaman()
    {
        // Hanya tampilkan peminjam aktif (Mahasiswa Aktif, Dosen, atau Pegawai/Tendik), jangan tampilkan Camaba / Alumni Lulus
        $users = User::with(['mahasiswa', 'dosen', 'tendik', 'roles'])
            ->where(function ($q) {
                $q->whereHas('mahasiswa', fn($m) => $m->where('status_mahasiswa', 'Aktif'))
                  ->orWhereHas('dosen')
                  ->orWhereHas('tendik')
                  ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['dosen', 'admin', 'pustakawan', 'keuangan', 'administrasi_umum', 'penjaminan_mutu', 'rektorat']));
            })
            ->orderBy('name')
            ->get();

        $koleksi = Koleksi::where('jumlah_tersedia', '>', 0)->orderBy('judul')->get();
        return view('perpustakaan.peminjaman.create', compact('users', 'koleksi'));
    }

    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id'         => 'required|exists:users,id',
            'eksemplar_ids'   => 'required|array|min:1',
            'eksemplar_ids.*' => 'required|distinct|exists:perpustakaan_eksemplars,id',
            'jatuh_tempo'     => 'required|date|after_or_equal:today',
        ], [
            'eksemplar_ids.required'   => 'Belum ada buku yang dipindai atau dipilih ke keranjang.',
            'eksemplar_ids.*.distinct' => 'Ada barcode yang masuk keranjang dua kali.',
            'eksemplar_ids.*.exists'   => 'Ada barcode yang tidak terdaftar di sistem.',
        ]);

        // Validasi status peminjam jika ia adalah mahasiswa
        $peminjamUser = User::with('mahasiswa')->findOrFail($request->user_id);
        if ($peminjamUser->mahasiswa && $peminjamUser->mahasiswa->status_mahasiswa !== 'Aktif') {
            return back()->withInput()->with('error', 'Peminjaman ditolak: Status akademik mahasiswa ini adalah ' . strtoupper($peminjamUser->mahasiswa->status_mahasiswa) . ' (Bukan Mahasiswa Aktif).');
        }

        DB::beginTransaction();
        try {
            $barcodeBerhasil = [];
            $judulDiKeranjang = [];
            $judulListNotif = [];

            foreach ($request->eksemplar_ids as $eksemplarId) {
                $eksemplar = PerpustakaanEksemplar::lockForUpdate()->find($eksemplarId);

                if (!$eksemplar) {
                    throw new \DomainException('Eksemplar buku tidak ditemukan.');
                }

                if ($eksemplar->status_fisik !== 'Tersedia') {
                    throw new \DomainException('Gagal meminjam. Eksemplar buku (Barcode: ' . $eksemplar->kode_barcode . ') sedang dipinjam atau tidak tersedia.');
                }

                if (in_array($eksemplar->koleksi_id, $judulDiKeranjang)) {
                    throw new \DomainException('Gagal meminjam. Ada dua eksemplar dari judul yang sama di keranjang (Barcode: ' . $eksemplar->kode_barcode . ').');
                }

                $buku = Koleksi::lockForUpdate()->find($eksemplar->koleksi_id);

                if (!$buku) {
                    throw new \DomainException('Gagal meminjam. Master buku untuk barcode ' . $eksemplar->kode_barcode . ' tidak ditemukan.');
                }

                if ($buku->jumlah_tersedia <= 0) {
                    throw new \DomainException('Gagal meminjam. Stok master buku "' . $buku->judul . '" sudah habis.');
                }

                $sudahPinjam = Peminjaman::where('user_id', $request->user_id)
                                         ->where('koleksi_id', $buku->id)
                                         ->where('status', 'Dipinjam')
                                         ->exists();

                if ($sudahPinjam) {
                    throw new \DomainException('Peminjam masih meminjam judul "' . $buku->judul . '" dan belum mengembalikannya.');
                }

                Peminjaman::create([
                    'user_id'        => $request->user_id,
                    'koleksi_id'     => $buku->id,
                    'eksemplar_id'   => $eksemplar->id,
                    'tanggal_pinjam' => now(),
                    'jatuh_tempo'    => $request->jatuh_tempo,
                    'status'         => 'Dipinjam',
                ]);

                $buku->decrement('jumlah_tersedia');
                $eksemplar->update(['status_fisik' => 'Dipinjam']);

                $judulDiKeranjang[] = $eksemplar->koleksi_id;
                $barcodeBerhasil[]  = $eksemplar->kode_barcode;
                $judulListNotif[]   = $buku->judul;
            }

            DB::commit();

            // Kirim notifikasi ke peminjam
            try {
                $peminjamUser->notify(new GeneralNotification(
                    'Peminjaman Buku Perpustakaan',
                    'Anda telah meminjam ' . count($judulListNotif) . ' buku (' . implode(', ', $judulListNotif) . '). Batas jatuh tempo pengembalian: ' . Carbon::parse($request->jatuh_tempo)->translatedFormat('d F Y') . '.',
                    route('dashboard'),
                    'bi-book-half text-primary'
                ));
            } catch (\Throwable $e) {
                // Abaikan jika gagal kirim notifikasi
            }

            return redirect()->route('perpustakaan.peminjaman.create')
                ->with('success', 'Transaksi berhasil! ' . count($barcodeBerhasil) . ' buku tercatat dipinjam oleh ' . $peminjamUser->name . ' (' . implode(', ', $barcodeBerhasil) . ').');

        } catch (\DomainException $e) {
            DB::rollback();
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal simpan peminjaman: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memproses peminjaman.');
        }
    }

    public function historyPeminjaman()
    {
        $peminjamans = Peminjaman::with(['user.mahasiswa', 'koleksi', 'eksemplar'])
            ->whereIn('status', ['Kembali', 'Dikembalikan'])
            ->orderBy('tanggal_kembali', 'desc')
            ->paginate(15);
            
        return view('perpustakaan.peminjaman.history', compact('peminjamans'));
    }

    public function returnForm()
    {
        $peminjamans = Peminjaman::with(['user.mahasiswa', 'user.dosen', 'koleksi', 'eksemplar'])
            ->where('status', 'Dipinjam')
            ->orderBy('jatuh_tempo', 'asc')
            ->get();

        return view('perpustakaan.peminjaman.return', compact('peminjamans'));
    }

    public function processReturn(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjamans,id',
        ]);

        $peminjaman = Peminjaman::with(['eksemplar', 'koleksi', 'user'])->findOrFail($request->peminjaman_id);

        if ($peminjaman->status !== 'Dipinjam') {
            return back()->with('error', 'Transaksi ini sudah diselesaikan sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $peminjaman->update([
                'tanggal_kembali' => now(),
                'status'          => 'Kembali',
            ]);

            if ($peminjaman->eksemplar_id) {
                $eksemplar = PerpustakaanEksemplar::lockForUpdate()->find($peminjaman->eksemplar_id);
                if ($eksemplar) {
                    $eksemplar->update(['status_fisik' => 'Tersedia']);
                }
            }

            $buku = Koleksi::lockForUpdate()->find($peminjaman->koleksi_id);
            if ($buku) {
                // Pastikan jumlah_tersedia tidak pernah melebihi jumlah_stok master
                $tersediaBaru = min((int) $buku->jumlah_stok, (int) $buku->jumlah_tersedia + 1);
                $buku->update(['jumlah_tersedia' => $tersediaBaru]);
            }

            DB::commit();

            if ($peminjaman->user) {
                try {
                    $peminjaman->user->notify(new GeneralNotification(
                        'Pengembalian Buku Selesai',
                        'Terima kasih, buku "' . ($peminjaman->koleksi->judul ?? '-') . '" telah tercatat dikembalikan ke Perpustakaan.',
                        route('dashboard'),
                        'bi-check-circle-fill text-success'
                    ));
                } catch (\Throwable $e) {}
            }
            
            $pesanSukses = 'Pengembalian buku "' . ($peminjaman->koleksi->judul ?? '-') . '" oleh ' . ($peminjaman->user->name ?? '-') . ' berhasil diproses.';
            if ($peminjaman->eksemplar) {
                $pesanSukses .= ' Eksemplar (' . $peminjaman->eksemplar->kode_barcode . ') kembali tersedia di rak.';
            }
            
            return redirect()->route('perpustakaan.peminjaman.returnForm')->with('success', $pesanSukses);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal proses pengembalian: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memproses pengembalian buku.');
        }
    }
}