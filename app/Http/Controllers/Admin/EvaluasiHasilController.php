<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiJawaban;
use App\Models\EvaluasiSesi;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluasiHasilController extends Controller
{
    /**
     * Menampilkan daftar rekapitulasi nilai dosen pada sesi tertentu.
     */
    public function index(Request $request)
    {
        // Ambil semua sesi untuk filter dropdown
        $sesiList = EvaluasiSesi::orderBy('created_at', 'desc')->get();

        // Tentukan sesi yang dipilih (default ke sesi terakhir/terbaru)
        $sesiId = $request->input('sesi_id', $sesiList->first()->id ?? 0);
        $sesiTerpilih = $sesiList->find($sesiId);

        // Query Aggregat: Mengelompokkan jawaban berdasarkan dosen
        // Menghitung rata-rata skor dan jumlah responden
        $hasilEvaluasi = [];
        
        if ($sesiTerpilih) {
            $hasilEvaluasi = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
                ->join('dosens', 'evaluasi_jawaban.dosen_id', '=', 'dosens.id') // Join untuk sorting nama
                ->select(
                    'evaluasi_jawaban.dosen_id',
                    'dosens.nama_lengkap as nama_dosen',
                    'dosens.nidn',
                    DB::raw('COUNT(DISTINCT evaluasi_jawaban.mahasiswa_id) as jumlah_responden'),
                    DB::raw('AVG(evaluasi_jawaban.jawaban_skala) as nilai_rata_rata')
                )
                ->whereNotNull('jawaban_skala') // Hanya hitung yang tipe skala
                ->groupBy('evaluasi_jawaban.dosen_id', 'dosens.nama_lengkap', 'dosens.nidn')
                ->orderBy('nilai_rata_rata', 'desc') // Urutkan dari nilai tertinggi
                ->paginate(10)
                ->withQueryString(); // Agar parameter filter tetap ada saat ganti halaman
        }

        return view('admin.evaluasi.hasil.index', compact('sesiList', 'sesiTerpilih', 'hasilEvaluasi'));
    }

    /**
     * Menampilkan detail evaluasi satu dosen (per pertanyaan & masukan teks).
     */
    public function show($sesiId, $dosenId)
    {
        $sesi = EvaluasiSesi::findOrFail($sesiId);
        $dosen = Dosen::findOrFail($dosenId);

        // 1. Ambil detail skor per pertanyaan
        $detailPerPertanyaan = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
            ->where('dosen_id', $dosenId)
            ->join('evaluasi_pertanyaan', 'evaluasi_jawaban.evaluasi_pertanyaan_id', '=', 'evaluasi_pertanyaan.id')
            ->select(
                'evaluasi_pertanyaan.pertanyaan',
                'evaluasi_pertanyaan.urutan',
                DB::raw('AVG(evaluasi_jawaban.jawaban_skala) as skor_rata_rata')
            )
            ->whereNotNull('jawaban_skala')
            ->groupBy('evaluasi_pertanyaan.id', 'evaluasi_pertanyaan.pertanyaan', 'evaluasi_pertanyaan.urutan')
            ->orderBy('evaluasi_pertanyaan.urutan')
            ->get();

        // 2. Ambil semua masukan teks (Saran/Kritik) dari mahasiswa
        $masukanTeks = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
            ->where('dosen_id', $dosenId)
            ->whereNotNull('jawaban_teks')
            ->where('jawaban_teks', '!=', '')
            ->select('jawaban_teks', 'created_at')
            ->latest()
            ->get();

        // 3. Hitung Total Rata-rata Akhir Dosen ini
        $totalRataRata = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
            ->where('dosen_id', $dosenId)
            ->whereNotNull('jawaban_skala')
            ->avg('jawaban_skala');

        return view('admin.evaluasi.hasil.show', compact('sesi', 'dosen', 'detailPerPertanyaan', 'masukanTeks', 'totalRataRata'));
    }
}