<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiJawaban;
use Illuminate\Support\Facades\DB;

class PenjaminanMutuController extends Controller
{
    /**
     * Menampilkan halaman dashboard untuk Penjaminan Mutu dengan data lengkap.
     */
    public function dashboard()
    {
        // --- UPDATE 1: PERHITUNGAN KPI ---
        $jumlahMahasiswaAktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $jumlahDosen = Dosen::count();
        
        // Menghitung rasio, menghindari pembagian dengan nol
        $rasioDosenMahasiswa = $jumlahMahasiswaAktif > 0 ? '1 : ' . round($jumlahMahasiswaAktif / $jumlahDosen, 1) : 'N/A';

        // --- UPDATE 2: DATA UNTUK GRAFIK ---
        // Grafik 1: Tren Mahasiswa Aktif per Angkatan
        $trenMahasiswaData = Mahasiswa::select('tahun_masuk', DB::raw('count(*) as total'))
            ->where('status_mahasiswa', 'Aktif')
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'asc')
            ->get();
        
        $trenMahasiswaLabels = $trenMahasiswaData->pluck('tahun_masuk');
        $trenMahasiswaTotals = $trenMahasiswaData->pluck('total');

        // Grafik 2: Distribusi Status Mahasiswa
        $distribusiStatusData = Mahasiswa::select('status_mahasiswa', DB::raw('count(*) as total'))
            ->groupBy('status_mahasiswa')
            ->get();
            
        $distribusiStatusLabels = $distribusiStatusData->pluck('status_mahasiswa');
        $distribusiStatusTotals = $distribusiStatusData->pluck('total');

        // --- UPDATE 3: LAPORAN RINGKAS HASIL EVALUASI DOSEN (EDOM) ---
        $sesiEdomAktif = EvaluasiSesi::where('is_active', 1)->first();
        $hasilEdom = [];

        if ($sesiEdomAktif) {
            $hasilEdom = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiEdomAktif->id)
                ->join('dosens', 'evaluasi_jawaban.dosen_id', '=', 'dosens.id')
                ->select('dosens.nama_lengkap', DB::raw('AVG(jawaban_skala) as rata_rata_skor'))
                ->groupBy('dosens.id', 'dosens.nama_lengkap') // Group by ID and name
                ->orderBy('rata_rata_skor', 'desc')
                ->limit(10) // Ambil 10 dosen teratas
                ->get();
        }

        // Mengirim semua data yang sudah diproses ke view
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
