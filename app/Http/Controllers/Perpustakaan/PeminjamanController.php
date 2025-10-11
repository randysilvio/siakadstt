<?php

namespace App\Http\Controllers\Perpustakaan;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\User;
use App\Models\Koleksi;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    public function index(): View
    {
        $peminjamans = Peminjaman::where('status', 'Dipinjam')
                                 ->with(['koleksi', 'user'])
                                 ->latest('tanggal_pinjam')
                                 ->paginate(15);
        return view('perpustakaan.peminjaman.index', compact('peminjamans'));
    }

    public function create(): View
    {
        $users = User::orderBy('name')->get();
        $koleksi = Koleksi::where('jumlah_tersedia', '>', 0)->orderBy('judul')->get();
        return view('perpustakaan.peminjaman.create', compact('users', 'koleksi'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'koleksi_id' => 'required|exists:perpustakaan_koleksi,id',
            'jatuh_tempo' => 'required|date|after_or_equal:today',
        ]);

        DB::beginTransaction();
        try {
            $buku = Koleksi::findOrFail($request->koleksi_id);
            if ($buku->jumlah_tersedia <= 0) {
                return back()->with('error', 'Maaf, stok buku ini sudah habis.');
            }

            Peminjaman::create([
                'user_id' => $request->user_id,
                'koleksi_id' => $request->koleksi_id,
                'tanggal_pinjam' => Carbon::today(),
                'jatuh_tempo' => $request->jatuh_tempo,
                'status' => 'Dipinjam',
            ]);
            $buku->decrement('jumlah_tersedia');
            DB::commit();

            return redirect()->route('perpustakaan.peminjaman.index')->with('success', 'Transaksi peminjaman berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memproses transaksi.');
        }
    }

    // METHOD BARU: Menampilkan form pengembalian
    public function showReturnForm(): View
    {
        $peminjamans = Peminjaman::where('status', 'Dipinjam')->with(['koleksi', 'user'])->get();
        return view('perpustakaan.peminjaman.return', compact('peminjamans'));
    }

    // METHOD BARU: Memproses data dari form pengembalian
    public function processReturn(Request $request): RedirectResponse
    {
        $request->validate(['peminjaman_id' => 'required|exists:peminjamans,id']);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::findOrFail($request->peminjaman_id);

            // Update status dan tanggal kembali
            $peminjaman->update([
                'status' => 'Kembali',
                'tanggal_kembali' => Carbon::today(),
            ]);

            // Tambah kembali jumlah buku yang tersedia
            $peminjaman->koleksi->increment('jumlah_tersedia');
            DB::commit();

            return redirect()->route('perpustakaan.peminjaman.index')->with('success', 'Buku berhasil dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memproses pengembalian.');
        }
    }

    // METHOD BARU: Menampilkan riwayat peminjaman
    public function history(): View
    {
        $peminjamans = Peminjaman::where('status', '!=', 'Dipinjam')
                                 ->with(['koleksi', 'user'])
                                 ->latest('tanggal_kembali')
                                 ->paginate(15);
        
        // Buat view untuk history, bisa duplikat dari index.blade.php
        return view('perpustakaan.peminjaman.history', compact('peminjamans'));
    }
}