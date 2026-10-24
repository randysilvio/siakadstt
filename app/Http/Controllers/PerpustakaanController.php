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
            ->where('jatuh_tempo', '<', Carbon::now())
            ->get();
        $terlambatCount = $peminjamanTerlambat->count();

        $aktivitasTerakhir = Peminjaman::with(['user', 'koleksi'])
            ->latest()
            ->take(5)
            ->get();

        $koleksiPopuler = Koleksi::query()
            ->when(Schema::hasColumn('perpustakaan_koleksi', 'total_pinjam'), function ($query) {
                return $query->orderBy('total_pinjam', 'desc');
            })
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
        $users = User::with(['mahasiswa', 'dosen', 'roles'])->orderBy('name')->get();
        $koleksi = Koleksi::where('jumlah_tersedia', '>', 0)->orderBy('judul')->get();
        return view('perpustakaan.peminjaman.create', compact('users', 'koleksi'));
    }

    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'eksemplar_ids' => 'required|array|min:1',
            'eksemplar_ids.*' => 'required|distinct|exists:perpustakaan_eksemplars,id',
            'jatuh_tempo' => 'required|date|after_or_equal:today',
        ], [
            'eksemplar_ids.required' => 'Belum ada buku yang dipindai atau dipilih ke keranjang.',
            'eksemplar_ids.*.distinct' => 'Ada barcode yang masuk keranjang dua kali.',
            'eksemplar_ids.*.exists' => 'Ada barcode yang tidak terdaftar di sistem.',
        ]);

        DB::beginTransaction();
        try {
            $barcodeBerhasil = [];
            $judulDiKeranjang = [];

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
                    throw new \DomainException('Pengguna masih meminjam judul "' . $buku->judul . '" dan belum mengembalikannya.');
                }

                Peminjaman::create([
                    'user_id' => $request->user_id,
                    'koleksi_id' => $buku->id,
                    'eksemplar_id' => $eksemplar->id,
                    'tanggal_pinjam' => now(),
                    'jatuh_tempo' => $request->jatuh_tempo,
                    'status' => 'Dipinjam',
                ]);

                $buku->decrement('jumlah_tersedia');
                $eksemplar->update(['status_fisik' => 'Dipinjam']);

                $judulDiKeranjang[] = $eksemplar->koleksi_id;
                $barcodeBerhasil[] = $eksemplar->kode_barcode;
            }

            DB::commit();

            return redirect()->route('perpustakaan.peminjaman.create')
                ->with('success', 'Transaksi berhasil. ' . count($barcodeBerhasil) . ' buku tercatat keluar: ' . implode(', ', $barcodeBerhasil) . '.');

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
                'status' => 'Kembali',
            ]);

            $buku = Koleksi::find($peminjaman->koleksi_id);
            if ($buku) {
                $buku->increment('jumlah_tersedia');
            }

            if ($peminjaman->eksemplar_id) {
                $eksemplar = PerpustakaanEksemplar::find($peminjaman->eksemplar_id);
                if ($eksemplar) {
                    $eksemplar->update(['status_fisik' => 'Tersedia']);
                }
            }

            DB::commit();
            
            $pesanSukses = 'Pengembalian buku "' . ($peminjaman->koleksi->judul ?? '-') . '" oleh ' . ($peminjaman->user->name ?? '-') . ' berhasil diproses.';
            if ($peminjaman->eksemplar) {
                $pesanSukses .= ' Eksemplar (' . $peminjaman->eksemplar->kode_barcode . ') kembali tersedia.';
            }
            
            // [PERBAIKAN UX STAY-ON-PAGE]: Tetap berada di halaman Pengembalian Buku agar kasir bisa langsung memproses buku berikutnya
            return redirect()->route('perpustakaan.peminjaman.returnForm')->with('success', $pesanSukses);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal proses pengembalian: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memproses pengembalian buku.');
        }
    }
}