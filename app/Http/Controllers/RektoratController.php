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
            $semesterAktif = $tahunAkademikAktif->tahun . ' ' . $tahunAkademikAktif->semester;
            
            $totalTagihanSemesterIni = Pembayaran::where('semester', $semesterAktif)->sum('jumlah');
            
            $pendapatanSemesterIni = Pembayaran::where('semester', $semesterAktif)
                                               ->where('status', 'lunas')
                                               ->sum('jumlah');
            
            $piutangSemesterIni = Pembayaran::where('semester', $semesterAktif)
                                            ->where('status', '!=', 'lunas')
                                            ->sum('jumlah');

            $rincianPendapatan = Pembayaran::where('semester', $semesterAktif)
                                           ->where('status', 'lunas')
                                           ->select('jenis_pembayaran', DB::raw('SUM(jumlah) as total_masuk'))
                                           ->groupBy('jenis_pembayaran')
                                           ->get();
        }

        // 3. TREN 5 TAHUN TERAKHIR
        $limaTahunLalu = $tahunIni - 4;
        
        $trenKeuangan = Pembayaran::where('status', 'lunas')
            ->select(DB::raw('YEAR(created_at) as tahun'), DB::raw('SUM(jumlah) as total_pendapatan'))
            ->whereYear('created_at', '>=', $limaTahunLalu)
            ->groupBy('tahun')
            ->orderBy('tahun', 'asc')
            ->pluck('total_pendapatan', 'tahun');

        $mahasiswaBaru = Mahasiswa::select('tahun_masuk', DB::raw('count(*) as total'))
            ->where('tahun_masuk', '>=', $limaTahunLalu)
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'asc')
            ->pluck('total', 'tahun_masuk');
            
        $lulusan = Mahasiswa::select(DB::raw('YEAR(tanggal_lulus) as tahun_lulus'), DB::raw('count(*) as total'))
            ->where('status_mahasiswa', 'Lulus')
            ->whereNotNull('tanggal_lulus')
            ->whereYear('tanggal_lulus', '>=', $limaTahunLalu)
            ->groupBy('tahun_lulus')
            ->orderBy('tahun_lulus', 'asc')
            ->pluck('total', 'tahun_lulus');

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