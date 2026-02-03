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
        // 1. Data Kuantitatif Utama
        $jumlahMahasiswaAktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $jumlahDosen = Dosen::count();
        
        $rasioDosenMahasiswa = $jumlahDosen > 0 ? '1 : ' . round($jumlahMahasiswaAktif / $jumlahDosen, 1) : 'N/A';

        // 2. [LOGIKA BARU] Hitung Rata-rata IPK Institusi (Real-time)
        // Mengambil semua mahasiswa aktif, hitung IPK masing-masing, lalu dirata-ratakan.
        $mahasiswas = Mahasiswa::with('mataKuliahs')
                        ->where('status_mahasiswa', 'Aktif')
                        ->get();

        $totalIpkKumulatif = 0;
        $jumlahMhsTerhitung = 0;

        foreach ($mahasiswas as $mhs) {
            $totalSks = 0;
            $totalBobot = 0;
            
            foreach ($mhs->mataKuliahs as $mk) {
                // Skip mata kuliah yang belum ada nilainya
                if (!$mk->pivot->nilai) continue;

                // Konversi Nilai Huruf ke Angka (Skala 4.0)
                $bobotNilai = match ($mk->pivot->nilai) {
                    'A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, default => 0
                };

                $sks = $mk->sks;
                $totalSks += $sks;
                $totalBobot += ($bobotNilai * $sks);
            }

            // Hanya hitung mahasiswa yang sudah memiliki SKS lulus
            if ($totalSks > 0) {
                $ipkMhs = $totalBobot / $totalSks;
                $totalIpkKumulatif += $ipkMhs;
                $jumlahMhsTerhitung++;
            }
        }

        // Jika belum ada nilai sama sekali, default 0.00
        $rataIPK = $jumlahMhsTerhitung > 0 ? round($totalIpkKumulatif / $jumlahMhsTerhitung, 2) : 0.00;

        // 3. Data Tren Mahasiswa (Grafik)
        $trenMahasiswaData = Mahasiswa::select('tahun_masuk', DB::raw('count(*) as total'))
            ->where('status_mahasiswa', 'Aktif')
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'asc')
            ->get();
        
        $trenMahasiswaLabels = $trenMahasiswaData->pluck('tahun_masuk');
        $trenMahasiswaTotals = $trenMahasiswaData->pluck('total');

        // 4. Data Distribusi Status (Pie Chart)
        $distribusiStatusData = Mahasiswa::select('status_mahasiswa', DB::raw('count(*) as total'))
            ->groupBy('status_mahasiswa')
            ->get();
            
        $distribusiStatusLabels = $distribusiStatusData->pluck('status_mahasiswa');
        $distribusiStatusTotals = $distribusiStatusData->pluck('total');

        // 5. Data Evaluasi Dosen (EDOM)
        $sesiEdomAktif = EvaluasiSesi::where('is_active', true)->first();
        $hasilEdom = collect();

        if ($sesiEdomAktif) {
            $hasilEdom = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiEdomAktif->id)
                ->join('dosens', 'evaluasi_jawaban.dosen_id', '=', 'dosens.id')
                ->select('dosens.nama_lengkap', 'dosens.id as dosen_id', DB::raw('AVG(jawaban_skala) as rata_rata_skor'))
                ->groupBy('dosens.id', 'dosens.nama_lengkap')
                ->orderBy('rata_rata_skor', 'desc')
                ->limit(10)
                ->get();
        }

        return view('penjaminan_mutu.dashboard', compact(
            'jumlahMahasiswaAktif',
            'jumlahDosen',
            'rasioDosenMahasiswa',
            'rataIPK', // <--- Variabel Dinamis
            'trenMahasiswaLabels',
            'trenMahasiswaTotals',
            'distribusiStatusLabels',
            'distribusiStatusTotals',
            'sesiEdomAktif',
            'hasilEdom'
        ));
    }
}