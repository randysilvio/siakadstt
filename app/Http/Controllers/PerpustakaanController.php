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
        // [FASE 2 UPDATE] Menambahkan validasi eksemplar_id dari hasil scan Barcode
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'koleksi_id' => 'required|exists:perpustakaan_koleksi,id',
            'eksemplar_id' => 'required|exists:perpustakaan_eksemplars,id', // Validasi Barcode
            'jatuh_tempo' => 'required|date|after_or_equal:today',
        ]);

        $buku = Koleksi::findOrFail($request->koleksi_id);

        if ($buku->jumlah_tersedia <= 0) {
            return back()->with('error', 'Gagal meminjam. Stok master buku sudah habis.');
        }

        // Cek apakah mahasiswa yang sama sudah meminjam eksemplar ini dan belum mengembalikan
        $sudahPinjam = Peminjaman::where('user_id', $request->user_id)
                                 ->where('koleksi_id', $request->koleksi_id)
                                 ->where('status', 'Dipinjam')
                                 ->exists();

        if ($sudahPinjam) {
            return back()->with('error', 'Pengguna masih meminjam judul buku ini dan belum mengembalikannya.');
        }

        // Gunakan DB Transaction agar jika satu proses gagal, semua dibatalkan (aman)
        DB::beginTransaction();
        try {
            // [PERBAIKAN] Ambil & kunci baris eksemplar di dalam transaksi agar tidak bisa dipinjam dobel
            $eksemplar = PerpustakaanEksemplar::lockForUpdate()->findOrFail($request->eksemplar_id);

            // [PERBAIKAN] Pastikan barcode memang milik judul buku yang dipinjam
            if ((int) $eksemplar->koleksi_id !== (int) $request->koleksi_id) {
                DB::rollback();
                return back()->with('error', 'Gagal meminjam. Barcode (' . $eksemplar->kode_barcode . ') tidak sesuai dengan judul buku.');
            }

            if ($eksemplar->status_fisik !== 'Tersedia') {
                DB::rollback();
                return back()->with('error', 'Gagal meminjam. Eksemplar buku (Barcode: ' . $eksemplar->kode_barcode . ') sedang dipinjam atau tidak tersedia.');
            }

            // 1. Buat catatan Peminjaman Baru
            Peminjaman::create([
                'user_id' => $request->user_id,
                'koleksi_id' => $request->koleksi_id,
                'eksemplar_id' => $request->eksemplar_id, // Simpan Eksemplar Fisik (Barcode)
                'tanggal_pinjam' => now(),
                'jatuh_tempo' => $request->jatuh_tempo,
                'status' => 'Dipinjam',
            ]);

            // 2. Kurangi stok general di master buku
            $buku->decrement('jumlah_tersedia');

            // 3. Ubah status fisik eksemplar menjadi 'Dipinjam'
            $eksemplar->update(['status_fisik' => 'Dipinjam']);

            DB::commit();
            return redirect()->route('perpustakaan.peminjaman.index')->with('success', 'Transaksi peminjaman berhasil. Eksemplar ('.$eksemplar->kode_barcode.') tercatat keluar.');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal simpan peminjaman: ' . $e->getMessage()); // [PERBAIKAN]
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses peminjaman.');
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