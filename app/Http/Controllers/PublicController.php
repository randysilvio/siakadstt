<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\Pengumuman;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Slideshow;
use App\Models\DokumenPublik;
use App\Models\KegiatanAkademik;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Menampilkan halaman publik utama (welcome) dengan struktur baru.
     */
    public function index(): View
    {
        // --- Data untuk Berita / Pengumuman ---
        // Ambil 4 berita terbaru untuk layout baru (1 utama + 3 sekunder)
        $beritaTerbaru = Pengumuman::where('target_role', 'semua')
                                   ->latest()
                                   ->take(4)
                                   ->get();
        // Pisahkan berita utama (pertama) dari berita lainnya
        $beritaUtama = $beritaTerbaru->first();
        $beritaLainnya = $beritaTerbaru->skip(1);

        // --- Data Lainnya (Tetap Sama) ---
        $slides = Slideshow::where('is_aktif', true)->orderBy('urutan')->get();
        $programStudi = ProgramStudi::orderBy('nama_prodi')->get();
        $totalMahasiswa = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $totalDosen = Dosen::count();
        $dokumen = DokumenPublik::latest()->take(5)->get();
        $kegiatanTerdekat = KegiatanAkademik::where('tanggal_mulai', '>=', now())
                                            ->orderBy('tanggal_mulai', 'asc')
                                            ->take(3)
                                            ->get();

        // --- Kirim semua data yang dibutuhkan ke view ---
        return view('welcome', [
            'slides' => $slides,
            'beritaUtama' => $beritaUtama,
            'beritaLainnya' => $beritaLainnya,
            'programStudi' => $programStudi,
            'totalMahasiswa' => $totalMahasiswa,
            'totalDosen' => $totalDosen,
            'dokumen' => $dokumen,
            'kegiatanTerdekat' => $kegiatanTerdekat,
        ]);
    }

    /**
     * Menampilkan halaman daftar semua berita/pengumuman dengan paginasi.
     */
    public function semuaBerita(): View
    {
        $semuaBerita = Pengumuman::where('target_role', 'semua')
                                ->latest()
                                ->paginate(9);
        return view('berita', compact('semuaBerita'));
    }

    /**
     * Menampilkan detail satu pengumuman untuk publik.
     */
    public function showPengumuman(Pengumuman $pengumuman): View
    {
        if ($pengumuman->target_role !== 'semua') {
            abort(404);
        }
        return view('pengumuman.public_show', compact('pengumuman'));
    }
}