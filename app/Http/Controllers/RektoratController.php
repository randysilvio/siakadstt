<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Pembayaran;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RektoratController extends Controller
{
    private function getLaporanData()
    {
        $tahunIni = Carbon::now()->year;
        $tahunAkademikAktif = TahunAkademik::where('is_active', 1)->first();

        // 1. METRIK MAHASISWA & LULUSAN
        $totalMahasiswaAktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $pendaftarTahunIni = Mahasiswa::where('tahun_masuk', $tahunIni)->count();
        $mahasiswaLulusTahunIni = Mahasiswa::where('status_mahasiswa', 'Lulus')
                                            ->whereYear('tanggal_lulus', $tahunIni)
                                            ->count();

        // 2. METRIK KEUANGAN & PIUTANG
        $totalTagihanSemesterIni = 0;
        $pendapatanSemesterIni = 0;
        $piutangSemesterIni = 0;
        $rincianPendapatan = collect();

        if ($tahunAkademikAktif) {
            // Tagihan semester aktif dikenali dari label baku maupun label lama yang diketik bebas
            $semesterIni = fn () => Pembayaran::untukSemester($tahunAkademikAktif);

            $totalTagihanSemesterIni = $semesterIni()->sum('jumlah');
            $pendapatanSemesterIni = $semesterIni()->where('status', 'lunas')->sum('jumlah');
            $piutangSemesterIni = $semesterIni()->where('status', '!=', 'lunas')->sum('jumlah');

            $rincianPendapatan = $semesterIni()
                                           ->where('status', 'lunas')
                                           ->select('jenis_pembayaran', DB::raw('SUM(jumlah) as total_masuk'))
                                           ->groupBy('jenis_pembayaran')
                                           ->get();
        }

        // 3. TREN 5 TAHUN TERAKHIR
        $limaTahunLalu = $tahunIni - 4;
        
        // Pendapatan dihitung pada tahun uang diterima (tanggal bayar), bukan tahun tagihan dibuat.
        // Pengelompokan di PHP agar berjalan di semua database (YEAR() khusus MySQL).
        $trenKeuangan = Pembayaran::where('status', 'lunas')
            ->where(fn ($q) => $q->whereDate('tanggal_bayar', '>=', "{$limaTahunLalu}-01-01")
                ->orWhere(fn ($q) => $q->whereNull('tanggal_bayar')->whereDate('created_at', '>=', "{$limaTahunLalu}-01-01")))
            ->get(['jumlah', 'tanggal_bayar', 'created_at'])
            ->groupBy(fn ($p) => (string) ($p->tanggal_bayar ?? $p->created_at)->year)
            ->map(fn ($grup) => $grup->sum('jumlah'));

        $mahasiswaBaru = Mahasiswa::select('tahun_masuk', DB::raw('count(*) as total'))
            ->where('tahun_masuk', '>=', $limaTahunLalu)
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'asc')
            ->pluck('total', 'tahun_masuk');
            
        $lulusan = Mahasiswa::where('status_mahasiswa', 'Lulus')
            ->whereNotNull('tanggal_lulus')
            ->whereDate('tanggal_lulus', '>=', "{$limaTahunLalu}-01-01")
            ->pluck('tanggal_lulus')
            ->countBy(fn ($tgl) => (int) \Carbon\Carbon::parse($tgl)->year);

        $grafikLabels = collect(range($limaTahunLalu, $tahunIni))->map(fn($year) => (string)$year);
        $grafikKeuanganData = $grafikLabels->map(fn($year) => $trenKeuangan->get($year, 0));
        $grafikMahasiswaBaruData = $grafikLabels->map(fn($year) => $mahasiswaBaru->get((int)$year, 0));
        $grafikLulusanData = $grafikLabels->map(fn($year) => $lulusan->get((int)$year, 0));

        // 4. KINERJA PROGRAM STUDI
        $kinerjaProdi = ProgramStudi::with('kaprodi.user')
            ->withCount([
                'mahasiswas as jumlah_mahasiswa_aktif' => function ($query) {
                    $query->where('status_mahasiswa', 'Aktif');
                },
                'dosens as jumlah_dosen' 
            ])
            ->get();

        return compact(
            'totalMahasiswaAktif', 'pendaftarTahunIni', 'mahasiswaLulusTahunIni', 
            'totalTagihanSemesterIni', 'pendapatanSemesterIni', 'piutangSemesterIni', 'rincianPendapatan',
            'grafikLabels', 'grafikKeuanganData', 'grafikMahasiswaBaruData', 'grafikLulusanData', 
            'kinerjaProdi', 'tahunAkademikAktif'
        );
    }

    public function dashboard(): View
    {
        $data = $this->getLaporanData();
        return view('rektorat.dashboard', $data);
    }

    public function cetakLaporan(): View
    {
        $data = $this->getLaporanData();
        return view('rektorat.cetak_laporan', $data);
    }
}