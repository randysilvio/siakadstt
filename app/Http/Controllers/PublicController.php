<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\Kurikulum;
use App\Models\MataKuliah;
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
    public function index(): View
    {
        $cfg = Pengaturan::allCached();

        $beritaTerbaru = Pengumuman::where('target_role', 'semua')
            ->latest()
            ->take(4)
            ->get();
        $beritaUtama = $beritaTerbaru->first();
        $beritaLainnya = $beritaTerbaru->skip(1);

        $slides = Slideshow::where('is_aktif', true)->orderBy('urutan')->get();
        $programStudi = ProgramStudi::with(['kaprodi'])
            ->withCount(['mahasiswas' => fn($q) => $q->where('status_mahasiswa', 'Aktif')])
            ->orderBy('nama_prodi')
            ->get();

        $totalMahasiswa = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $totalAlumni    = Mahasiswa::where('status_mahasiswa', 'Lulus')->count();
        $totalDosen     = Dosen::count();
        $totalKoleksi   = Koleksi::count();

        $today = Carbon::today();
        $periodePmbAktif = PmbPeriod::where('is_active', true)
            ->whereDate('tanggal_buka', '<=', $today)
            ->whereDate('tanggal_tutup', '>=', $today)
            ->first();

        $risetTerbaru = DosenTridharma::with('dosen.programStudi')
            ->whereIn('unsur', ['penelitian', 'pengabdian'])
            ->orderBy('tahun', 'desc')
            ->latest()
            ->take(4)
            ->get();

        $koleksiTerbaru = Koleksi::latest()->take(4)->get();
        $dokumen = DokumenPublik::latest()->take(5)->get();
        // Agenda di beranda publik hanya yang ditujukan bagi mahasiswa (agenda internal staf/dosen tidak ditampilkan)
        $kegiatanTerdekat = KegiatanAkademik::where('tanggal_mulai', '>=', now()->subDays(7))
            ->whereHas('roles', fn ($r) => $r->whereIn('name', ['mahasiswa', 'camaba']))
            ->orderBy('tanggal_mulai', 'asc')
            ->take(4)
            ->get();

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
     * Halaman Publik: Detail Program Studi, Dosen Homebase, & Kurikulum Semester 1-8.
     */
    public function showProdi(ProgramStudi $programStudi): View
    {
        $cfg = Pengaturan::allCached();
        $programStudi->load(['kaprodi', 'fakultas', 'dosens']);
        $jumlahMahasiswaAktif = $programStudi->mahasiswas()->where('status_mahasiswa', 'Aktif')->count();
        $jumlahAlumni = $programStudi->mahasiswas()->where('status_mahasiswa', 'Lulus')->count();

        // Kurikulum milik prodi ini (yang aktif didahulukan) atau kurikulum umum tanpa prodi.
        // Tidak boleh jatuh ke kurikulum prodi lain agar halaman publik tidak menampilkan mata kuliah yang salah.
        $kurikulum = Kurikulum::where('program_studi_id', $programStudi->id)
            ->orderBy('is_active', 'desc')
            ->orderBy('tahun', 'desc')
            ->first()
            ?? Kurikulum::whereNull('program_studi_id')->where('is_active', true)->orderBy('tahun', 'desc')->first();

        $mataKuliahsPerSemester = collect();
        if ($kurikulum) {
            $mataKuliahsPerSemester = MataKuliah::with('dosen')
                ->where('kurikulum_id', $kurikulum->id)
                ->orderBy('semester')
                ->orderBy('kode_mk')
                ->get()
                ->groupBy('semester');
        }


        $semuaProdi = ProgramStudi::orderBy('nama_prodi')->get();

        return view('public.prodi_show', compact(
            'cfg',
            'programStudi',
            'jumlahMahasiswaAktif',
            'jumlahAlumni',
            'kurikulum',
            'mataKuliahsPerSemester',
            'semuaProdi'
        ));
    }

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

    public function semuaBerita(): View
    {
        $semuaBerita = Pengumuman::where('target_role', 'semua')
            ->latest()
            ->paginate(9);
        return view('berita', compact('semuaBerita'));
    }

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

    public function dokumenPublik(Request $request): View
    {
        $semuaDokumen = DokumenPublik::latest()->get();
        $dokumenPerKategori = $semuaDokumen->groupBy(fn($item) => $item->kategori ?: 'Umum');
        $kategoris = $dokumenPerKategori->keys();

        return view('dokumen_publik', compact('kategoris', 'dokumenPerKategori'));
    }
}