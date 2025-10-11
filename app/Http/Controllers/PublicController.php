<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\Pengumuman;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Slideshow;
use App\Models\DokumenPublik;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Menampilkan halaman publik utama (welcome).
     */
    public function index(): View
    {
        // Data untuk Slideshow, diurutkan berdasarkan kolom 'urutan'
        $slides = Slideshow::where('is_aktif', true)->orderBy('urutan')->get();

        // Data untuk Berita Terbaru di halaman utama
        $beritaTerbaru = Pengumuman::where('target_role', 'semua')
                                   ->latest()
                                   ->take(3)
                                   ->get();
        
        // Data untuk Program Studi
        $programStudi = ProgramStudi::orderBy('nama_prodi')->get();

        // Data untuk Statistik
        $totalMahasiswa = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $totalDosen = Dosen::count();

        // Data untuk Dokumen Publik
        $dokumen = DokumenPublik::latest()->take(5)->get();

        // Kirim semua data ke view
        return view('welcome', [
            'slides' => $slides,
            'berita' => $beritaTerbaru,
            'programStudi' => $programStudi,
            'totalMahasiswa' => $totalMahasiswa,
            'totalDosen' => $totalDosen,
            'dokumen' => $dokumen,
        ]);
    }

    /**
     * Menampilkan halaman daftar semua berita/pengumuman dengan paginasi.
     */
    public function semuaBerita(): View
    {
        $semuaBerita = Pengumuman::where('target_role', 'semua')
                                ->latest()
                                ->paginate(9); // Menampilkan 9 berita per halaman
                                
        return view('berita', compact('semuaBerita'));
    }

    /**
     * Menampilkan detail satu pengumuman untuk publik.
     * Fungsi ini akan menangani rute 'pengumuman.public.show'.
     */
    public function showPengumuman(Pengumuman $pengumuman): View
    {
        // --- BARIS PERBAIKAN ---
        // Tambahkan pemeriksaan untuk memastikan hanya pengumuman publik yang bisa dibuka.
        if ($pengumuman->target_role !== 'semua') {
            abort(404); // Tampilkan halaman "Not Found" jika bukan untuk publik.
        }
        // --- AKHIR DARI PERBAIKAN ---
        
        return view('pengumuman.public_show', compact('pengumuman'));
    }
}