<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema; // Import Schema facade
use App\Models\Koleksi;      
use App\Models\Peminjaman;   
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
        // === DIPERBAIKI ===
        // Menggunakan nama kolom 'jumlah_stok' yang benar sesuai database Anda.
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

        // === DIPERBAIKI ===
        // Kueri ini sekarang aman. Ia hanya akan mengurutkan berdasarkan 'total_pinjam'
        // jika kolom tersebut memang ada di tabel Anda untuk menghindari error.
        $koleksiPopuler = Koleksi::query()
            ->when(Schema::hasColumn('perpustakaan_koleksi', 'total_pinjam'), function ($query) {
                return $query->orderBy('total_pinjam', 'desc');
            })
            ->take(5)
            ->get();

        // Mengirim semua data yang dibutuhkan oleh view
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
}