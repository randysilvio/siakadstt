<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\Pengumuman;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\DosenTridharma;
use App\Models\Koleksi;
use App\Models\PmbPeriod;
use App\Models\TracerStudy;
use App\Models\Slideshow;
use App\Models\DokumenPublik;
use App\Models\KegiatanAkademik;
use App\Models\Pengaturan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Menampilkan halaman publik utama (welcome) gaya UKSW Enterprise.
     */
    public function index(): View
    {
        $cfg = Pengaturan::allCached();

        // 1. Berita & Pengumuman Publik
        $beritaTerbaru = Pengumuman::where('target_role', 'semua')
            ->latest()
            ->take(4)
            ->get();
        $beritaUtama = $beritaTerbaru->first();
        $beritaLainnya = $beritaTerbaru->skip(1);

        // 2. Slideshow & Program Studi (beserta Kaprodi, jumlah mahasiswa aktif, & mata kuliah)
        $slides = Slideshow::where('is_aktif', true)->orderBy('urutan')->get();
        $programStudi = ProgramStudi::with(['kaprodi'])
            ->withCount(['mahasiswas' => fn($q) => $q->where('status_mahasiswa', 'Aktif')])
            ->orderBy('nama_prodi')
            ->get();

        // 3. Statistik Impact Kampus
        $totalMahasiswa = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $totalAlumni    = Mahasiswa::where('status_mahasiswa', 'Lulus')->count();
        $totalDosen     = Dosen::count();
        $totalKoleksi   = Koleksi::count();

        // 4. Gelombang PMB yang sedang aktif
        $today = Carbon::today();
        $periodePmbAktif = PmbPeriod::where('is_active', true)
            ->whereDate('tanggal_buka', '<=', $today)
            ->whereDate('tanggal_tutup', '>=', $today)
            ->first();

        // 5. Sorotan Riset, Publikasi Ilmiah & Pengabdian Masyarakat Dosen (Tridharma)
        $risetTerbaru = DosenTridharma::with('dosen.programStudi')
            ->whereIn('unsur', ['penelitian', 'pengabdian'])
            ->orderBy('tahun', 'desc')
            ->latest()
            ->take(4)
            ->get();

        // 6. Koleksi Buku Perpustakaan Terbaru (OPAC Preview)
        $koleksiTerbaru = Koleksi::latest()->take(4)->get();

        // 7. Dokumen Publik & Agenda Kegiatan Terdekat
        $dokumen = DokumenPublik::latest()->take(5)->get();
        $kegiatanTerdekat = KegiatanAkademik::where('tanggal_mulai', '>=', now()->subDays(7))
            ->orderBy('tanggal_mulai', 'asc')
            ->take(4)
            ->get();

        // 8. Ringkasan Tracer Study Alumni
        $totalTracer = TracerStudy::count();
        $persenSerapanAlumni = $totalTracer > 0
            ? round((TracerStudy::where('status_pekerjaan', '!=', 'Belum Bekerja')->count() / $totalTracer) * 100)
            : 94;

        return view('welcome', compact(
            'cfg',
            'slides',
            'beritaUtama',
            'beritaLainnya',
            'programStudi',
            'totalMahasiswa',
            'totalAlumni',
            'totalDosen',
            'totalKoleksi',
            'periodePmbAktif',
            'risetTerbaru',
            'koleksiTerbaru',
            'dokumen',
            'kegiatanTerdekat',
            'persenSerapanAlumni'
        ));
    }

    /**
     * Halaman Publik: Profil Institusi, Sejarah, Visi-Misi, & Struktur Organisasi (Sinode/Yayasan, Senat, Kampus, & Prodi).
     */
    public function profilKampus(): View
    {
        $cfg = Pengaturan::allCached();
        $programStudis = ProgramStudi::with(['kaprodi', 'dosens'])
            ->withCount(['mahasiswas' => fn($q) => $q->where('status_mahasiswa', 'Aktif')])
            ->orderBy('nama_prodi')
            ->get();
        $totalDosen = Dosen::count();
        $totalMahasiswa = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();

        return view('public.profil_kampus', compact('cfg', 'programStudis', 'totalDosen', 'totalMahasiswa'));
    }

    /**
     * Halaman Publik: Etalase Riset, Publikasi Ilmiah & Pengabdian Masyarakat (Tridharma Dosen).
     */
    public function risetPublikasi(Request $request): View
    {
        $cfg = Pengaturan::allCached();
        $query = DosenTridharma::with(['dosen.programStudi'])
            ->whereIn('unsur', ['penelitian', 'pengabdian'])
            ->orderBy('tahun', 'desc')
            ->latest();

        if ($request->filled('unsur') && in_array($request->unsur, ['penelitian', 'pengabdian'])) {
            $query->where('unsur', $request->unsur);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('judul', 'like', "%{$s}%")
                  ->orWhere('kategori', 'like', "%{$s}%")
                  ->orWhere('nama_jurnal_penerbit_lokasi', 'like', "%{$s}%")
                  ->orWhereHas('dosen', fn($d) => $d->where('nama_lengkap', 'like', "%{$s}%"));
            });
        }

        $karyaTridharmas = $query->paginate(12)->withQueryString();
        $tahunList = DosenTridharma::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $totalPenelitian = DosenTridharma::where('unsur', 'penelitian')->count();
        $totalPengabdian = DosenTridharma::where('unsur', 'pengabdian')->count();

        return view('public.riset_publikasi', compact(
            'cfg',
            'karyaTridharmas',
            'tahunList',
            'totalPenelitian',
            'totalPengabdian'
        ));
    }

    /**
     * Halaman Publik: Katalog Perpustakaan Digital (OPAC - Online Public Access Catalog).
     */
    public function katalogPerpustakaan(Request $request): View
    {
        $cfg = Pengaturan::allCached();
        $query = Koleksi::withCount('eksemplars')->orderBy('judul', 'asc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('judul', 'like', "%{$s}%")
                  ->orWhere('pengarang', 'like', "%{$s}%")
                  ->orWhere('penerbit', 'like', "%{$s}%")
                  ->orWhere('isbn', 'like', "%{$s}%")
                  ->orWhere('lokasi_rak', 'like', "%{$s}%");
            });
        }

        if ($request->input('ketersediaan') === 'tersedia') {
            $query->where('jumlah_tersedia', '>', 0);
        }

        $koleksis = $query->paginate(12)->withQueryString();
        $totalJudul = Koleksi::count();
        $totalEksemplar = Koleksi::sum('jumlah_stok');

        return view('public.katalog_perpustakaan', compact('cfg', 'koleksis', 'totalJudul', 'totalEksemplar'));
    }

    /**
     * Menampilkan halaman daftar semua berita/pengumuman.
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

        $beritaTerkait = Pengumuman::where('target_role', 'semua')
            ->where('id', '!=', $pengumuman->id)
            ->latest()
            ->take(4)
            ->get();

        return view('pengumuman.public_show', [
            'pengumuman'    => $pengumuman,
            'beritaTerkait' => $beritaTerkait,
        ]);
    }

    /**
     * Menampilkan halaman daftar semua dokumen publik.
     */
    public function dokumenPublik(Request $request): View
    {
        $query = DokumenPublik::latest();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        $dokumens = $query->paginate(12)->withQueryString();
        return view('dokumen_publik', compact('dokumens'));
    }
}