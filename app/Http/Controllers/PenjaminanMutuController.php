<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\EvaluasiJawaban;
use App\Models\EvaluasiSesi;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

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

        // 2. Hitung Rata-rata IPK Institusi (Real-time untuk Mahasiswa Aktif)
        $mahasiswas = Mahasiswa::with('mataKuliahs')
                        ->where('status_mahasiswa', 'Aktif')
                        ->get();

        $totalIpkKumulatif = 0;
        $jumlahMhsTerhitung = 0;

        foreach ($mahasiswas as $mhs) {
            $ipkMhs = $mhs->hitungIpk(); // Menggunakan fungsi bawaan dari Model Mahasiswa
            if ($ipkMhs > 0) {
                $totalIpkKumulatif += $ipkMhs;
                $jumlahMhsTerhitung++;
            }
        }

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

        // 6. [LOGIKA BARU] Profil Kelulusan (Standar Akreditasi BAN-PT)
        $dataLulusan = Mahasiswa::with('mataKuliahs')
            ->where('status_mahasiswa', 'Lulus')
            ->get()
            ->groupBy(function($item) {
                // Fallback ke updated_at JIKA kolom tanggal lulus (baru) ada yang terlanjur kosong di masa lalu
                return $item->tanggal_lulus ? Carbon::parse($item->tanggal_lulus)->format('Y') : $item->updated_at->format('Y'); 
            })
            ->map(function ($mahasiswas, $tahunLulus) {
                $jumlahLulusan = $mahasiswas->count();
                $totalIpk = 0;
                $totalMasaStudi = 0;

                foreach($mahasiswas as $mhs) {
                    $totalIpk += $mhs->hitungIpk();
                    
                    // Masa studi = Tahun Lulus - Tahun Masuk
                    $masaStudi = (int)$tahunLulus - (int)$mhs->tahun_masuk;
                    // Minimal masa studi dianggap 1 tahun untuk menghindari angka 0
                    $totalMasaStudi += max($masaStudi, 1); 
                }

                return (object) [
                    'tahun_lulus' => $tahunLulus,
                    'jumlah' => $jumlahLulusan,
                    'rata_ipk' => round($totalIpk / $jumlahLulusan, 2),
                    'rata_masa_studi' => round($totalMasaStudi / $jumlahLulusan, 1)
                ];
            })
            ->sortByDesc('tahun_lulus') // Urutkan dari tahun terbaru
            ->values();

        return view('penjaminan_mutu.dashboard', compact(
            'jumlahMahasiswaAktif',
            'jumlahDosen',
            'rasioDosenMahasiswa',
            'rataIPK',
            'trenMahasiswaLabels',
            'trenMahasiswaTotals',
            'distribusiStatusLabels',
            'distribusiStatusTotals',
            'sesiEdomAktif',
            'hasilEdom',
            'dataLulusan' // <-- Variabel baru dikirim ke view
        ));
    }
}