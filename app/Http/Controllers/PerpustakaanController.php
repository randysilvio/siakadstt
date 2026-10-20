<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Tambahan untuk transaksi database yang aman
use Illuminate\Support\Facades\Log; // [PERBAIKAN] untuk mencatat error
use App\Models\Koleksi;      
use App\Models\Peminjaman;   
use App\Models\User;
use App\Models\PerpustakaanEksemplar; // [TAMBAHAN FASE 2]
use Carbon\Carbon;

class PerpustakaanController extends Controller
{
    /**
     * Menampilkan halaman dashboard khusus untuk pustakawan.
     */
    public function dashboard(): View
    {
        // 1. Statistik Utama
        $totalJudul = Koleksi::count();
        $totalEksemplar = Koleksi::sum('jumlah_stok'); 
        $peminjamanAktif = Peminjaman::where('status', 'Dipinjam')->count();

        // 2. Data Peminjaman Terlambat
        $peminjamanTerlambat = Peminjaman::with(['koleksi', 'user'])
            ->where('status', 'Dipinjam')
            ->where('jatuh_tempo', '<', Carbon::now())
            ->get();
        $terlambatCount = $peminjamanTerlambat->count();

        // 3. Aktivitas Sirkulasi Terakhir
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

    /**
     * =========================================================
     * FUNGSI SIRKULASI PEMINJAMAN
     * =========================================================
     */
    
    public function indexPeminjaman()
    {
        // Tampilkan peminjaman yang sedang aktif
        $peminjamans = Peminjaman::with(['user', 'koleksi', 'eksemplar']) // eager load eksemplar
            ->where('status', 'Dipinjam')
            ->orderBy('jatuh_tempo', 'asc')
            ->paginate(15);
            
        return view('perpustakaan.peminjaman.index', compact('peminjamans'));
    }

    public function createPeminjaman()
    {
        $users = User::all();
        $koleksi = Koleksi::where('jumlah_tersedia', '>', 0)->get();
        return view('perpustakaan.peminjaman.create', compact('users', 'koleksi'));
    }

    public function storePeminjaman(Request $request)
    {
        // [MODE KASIR] Menerima banyak eksemplar sekaligus dari keranjang scan
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'eksemplar_ids' => 'required|array|min:1',
            'eksemplar_ids.*' => 'required|distinct|exists:perpustakaan_eksemplars,id',
            'jatuh_tempo' => 'required|date|after_or_equal:today',
        ], [
            'eksemplar_ids.required' => 'Belum ada buku yang dipindai ke keranjang.',
            'eksemplar_ids.*.distinct' => 'Ada barcode yang masuk keranjang dua kali.',
            'eksemplar_ids.*.exists' => 'Ada barcode yang tidak terdaftar di sistem.',
        ]);

        // Gunakan DB Transaction: jika satu buku gagal, semua dibatalkan (aman)
        DB::beginTransaction();
        try {
            $barcodeBerhasil = [];
            $judulDiKeranjang = [];

            foreach ($request->eksemplar_ids as $eksemplarId) {
                // Kunci baris eksemplar agar tidak bisa dipinjam dobel secara bersamaan
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

                // Cek apakah pengguna masih meminjam judul yang sama dan belum mengembalikan
                $sudahPinjam = Peminjaman::where('user_id', $request->user_id)
                                         ->where('koleksi_id', $buku->id)
                                         ->where('status', 'Dipinjam')
                                         ->exists();

                if ($sudahPinjam) {
                    throw new \DomainException('Pengguna masih meminjam judul "' . $buku->judul . '" dan belum mengembalikannya.');
                }

                // 1. Buat catatan Peminjaman Baru
                Peminjaman::create([
                    'user_id' => $request->user_id,
                    'koleksi_id' => $buku->id,
                    'eksemplar_id' => $eksemplar->id, // Simpan Eksemplar Fisik (Barcode)
                    'tanggal_pinjam' => now(),
                    'jatuh_tempo' => $request->jatuh_tempo,
                    'status' => 'Dipinjam',
                ]);

                // 2. Kurangi stok general di master buku
                $buku->decrement('jumlah_tersedia');

                // 3. Ubah status fisik eksemplar menjadi 'Dipinjam'
                $eksemplar->update(['status_fisik' => 'Dipinjam']);

                $judulDiKeranjang[] = $eksemplar->koleksi_id;
                $barcodeBerhasil[] = $eksemplar->kode_barcode;
            }

            DB::commit();

            // [MODE KASIR] Kembali ke halaman scan agar bisa langsung melayani transaksi berikutnya
            return redirect()->route('perpustakaan.peminjaman.create')
                ->with('success', 'Transaksi berhasil. ' . count($barcodeBerhasil) . ' buku tercatat keluar: ' . implode(', ', $barcodeBerhasil) . '.');

        } catch (\DomainException $e) {
            DB::rollback();
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal simpan peminjaman: ' . $e->getMessage()); // [PERBAIKAN]
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memproses peminjaman.');
        }
    }

    public function historyPeminjaman()
    {
        // Tampilkan peminjaman yang sudah kembali/selesai
        $peminjamans = Peminjaman::with(['user', 'koleksi', 'eksemplar'])
            ->whereIn('status', ['Kembali', 'Dikembalikan']) // Beberapa sistem pakai 'Dikembalikan'
            ->orderBy('tanggal_kembali', 'desc')
            ->paginate(15);
            
        return view('perpustakaan.peminjaman.history', compact('peminjamans'));
    }

    public function returnForm()
    {
        // Hanya ambil transaksi yang belum dikembalikan
        $peminjamans = Peminjaman::with(['user', 'koleksi', 'eksemplar'])
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

        $peminjaman = Peminjaman::findOrFail($request->peminjaman_id);

        if ($peminjaman->status !== 'Dipinjam') {
            return back()->with('error', 'Transaksi ini sudah diselesaikan sebelumnya.');
        }

        DB::beginTransaction();
        try {
            // 1. Update status peminjaman selesai
            $peminjaman->update([
                'tanggal_kembali' => now(),
                'status' => 'Kembali', // Status selesai
            ]);

            // 2. Tambah kembali stok master buku (hanya jika master bukunya belum dihapus)
            $buku = Koleksi::find($peminjaman->koleksi_id);
            if ($buku) {
                $buku->increment('jumlah_tersedia');
            }

            // 3. [FASE 2 UPDATE] Kembalikan status fisik eksemplar ke 'Tersedia'
            if ($peminjaman->eksemplar_id) {
                $eksemplar = PerpustakaanEksemplar::find($peminjaman->eksemplar_id);
                if ($eksemplar) {
                    $eksemplar->update(['status_fisik' => 'Tersedia']);
                }
            }

            DB::commit();
            
            $pesanSukses = 'Pengembalian buku berhasil diproses. Stok fisik pulih.';
            if($peminjaman->eksemplar) {
                 $pesanSukses = 'Pengembalian buku berhasil. Eksemplar ('.$peminjaman->eksemplar->kode_barcode.') kembali tersedia.';
            }
            
            return redirect()->route('perpustakaan.peminjaman.index')->with('success', $pesanSukses);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal proses pengembalian: ' . $e->getMessage()); // [PERBAIKAN]
            return back()->with('error', 'Terjadi kesalahan saat memproses pengembalian buku.');
        }
    }
}