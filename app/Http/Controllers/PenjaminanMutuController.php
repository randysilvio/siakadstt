<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\EvaluasiJawaban;
use App\Models\EvaluasiSesi;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenjaminanMutuController extends Controller
{
    /**
     * Menampilkan halaman dashboard untuk Penjaminan Mutu.
     */
    public function dashboard(): View
    {
        $jumlahMahasiswaAktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $jumlahDosen = Dosen::count();
        
        $rasioDosenMahasiswa = $jumlahDosen > 0 ? '1 : ' . round($jumlahMahasiswaAktif / $jumlahDosen, 1) : 'N/A';

        $trenMahasiswaData = Mahasiswa::select('tahun_masuk', DB::raw('count(*) as total'))
            ->where('status_mahasiswa', 'Aktif')
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'asc')
            ->get();
        
        $trenMahasiswaLabels = $trenMahasiswaData->pluck('tahun_masuk');
        $trenMahasiswaTotals = $trenMahasiswaData->pluck('total');

        $distribusiStatusData = Mahasiswa::select('status_mahasiswa', DB::raw('count(*) as total'))
            ->groupBy('status_mahasiswa')
            ->get();
            
        $distribusiStatusLabels = $distribusiStatusData->pluck('status_mahasiswa');
        $distribusiStatusTotals = $distribusiStatusData->pluck('total');

        $sesiEdomAktif = EvaluasiSesi::where('is_active', true)->first();
        $hasilEdom = collect();

        if ($sesiEdomAktif) {
            $hasilEdom = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiEdomAktif->id)
                ->join('dosens', 'evaluasi_jawaban.dosen_id', '=', 'dosens.id')
                ->select('dosens.nama_lengkap', DB::raw('AVG(jawaban_skala) as rata_rata_skor'))
                ->groupBy('dosens.id', 'dosens.nama_lengkap')
                ->orderBy('rata_rata_skor', 'desc')
                ->limit(10)
                ->get();
        }

        return view('penjaminan_mutu.dashboard', compact(
            'jumlahMahasiswaAktif',
            'jumlahDosen',
            'rasioDosenMahasiswa',
            'trenMahasiswaLabels',
            'trenMahasiswaTotals',
            'distribusiStatusLabels',
            'distribusiStatusTotals',
            'sesiEdomAktif',
            'hasilEdom'
        ));
    }
}