<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RektoratController extends Controller
{
    /**
     * Menampilkan halaman dashboard untuk Rektorat dengan data eksekutif lengkap.
     */
    public function dashboard(): View
    {
        $tahunIni = Carbon::now()->year;
        $tahunAkademikAktif = TahunAkademik::where('is_active', 1)->first();

        $totalMahasiswaAktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $pendaftarTahunIni = Mahasiswa::where('tahun_masuk', $tahunIni)->count();
        
        $pendapatanSemesterIni = 0;
        if ($tahunAkademikAktif) {
            $pendapatanSemesterIni = Pembayaran::where('semester', $tahunAkademikAktif->tahun . ' ' . $tahunAkademikAktif->semester)
                                                ->where('status', 'lunas')
                                                ->sum('jumlah');
        }
        
        $mahasiswaLulusTahunIni = Mahasiswa::where('status_mahasiswa', 'Lulus')
                                            ->whereYear('updated_at', $tahunIni)
                                            ->count();

        $limaTahunLalu = $tahunIni - 4;
        
        $trenKeuangan = Pembayaran::where('status', 'lunas')
            ->select(DB::raw('LEFT(semester, 4) as tahun'), DB::raw('SUM(jumlah) as total_pendapatan'))
            ->where(DB::raw('LEFT(semester, 4)'), '>=', $limaTahunLalu)
            ->groupBy('tahun')
            ->orderBy('tahun', 'asc')
            ->pluck('total_pendapatan', 'tahun');

        $mahasiswaBaru = Mahasiswa::select('tahun_masuk', DB::raw('count(*) as total'))
            ->where('tahun_masuk', '>=', $limaTahunLalu)
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'asc')
            ->pluck('total', 'tahun_masuk');
            
        $lulusan = Mahasiswa::where('status_mahasiswa', 'Lulus')
            ->select(DB::raw('YEAR(updated_at) as tahun_lulus'), DB::raw('count(*) as total'))
            ->whereYear('updated_at', '>=', $limaTahunLalu)
            ->groupBy('tahun_lulus')
            ->orderBy('tahun_lulus', 'asc')
            ->pluck('total', 'tahun_lulus');

        $labelsTahun = collect(range($limaTahunLalu, $tahunIni))->map(fn($year) => (string)$year);
        $dataKeuangan = $labelsTahun->map(fn($year) => $trenKeuangan->get($year, 0));
        $dataMahasiswaBaru = $labelsTahun->map(fn($year) => $mahasiswaBaru->get((int)$year, 0));
        $dataLulusan = $labelsTahun->map(fn($year) => $lulusan->get((int)$year, 0));

        $kinerjaProdi = ProgramStudi::with('kaprodi.user')
            ->withCount(['mahasiswas as jumlah_mahasiswa_aktif' => function ($query) {
                $query->where('status_mahasiswa', 'Aktif');
            }])
            ->get();

        return view('rektorat.dashboard', compact(
            'totalMahasiswaAktif',
            'pendaftarTahunIni',
            'pendapatanSemesterIni',
            'mahasiswaLulusTahunIni',
            'labelsTahun',
            'dataKeuangan',
            'dataMahasiswaBaru',
            'dataLulusan',
            'kinerjaProdi'
        ));
    }
}