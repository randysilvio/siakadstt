<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiJawaban;
use App\Models\EvaluasiSesi;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // Tambahkan ini

class EvaluasiHasilController extends Controller
{
    public function index(Request $request)
    {
        $sesiList = EvaluasiSesi::orderBy('created_at', 'desc')->get();
        $sesiId = $request->input('sesi_id', $sesiList->first()->id ?? 0);
        $sesiTerpilih = $sesiList->find($sesiId);

        $hasilEvaluasi = [];
        
        if ($sesiTerpilih) {
            $hasilEvaluasi = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
                ->join('dosens', 'evaluasi_jawaban.dosen_id', '=', 'dosens.id')
                ->select(
                    'evaluasi_jawaban.dosen_id',
                    'dosens.nama_lengkap as nama_dosen',
                    'dosens.nidn',
                    DB::raw('COUNT(DISTINCT evaluasi_jawaban.mahasiswa_id) as jumlah_responden'),
                    DB::raw('AVG(evaluasi_jawaban.jawaban_skala) as nilai_rata_rata')
                )
                ->whereNotNull('jawaban_skala')
                ->groupBy('evaluasi_jawaban.dosen_id', 'dosens.nama_lengkap', 'dosens.nidn')
                ->orderBy('nilai_rata_rata', 'desc')
                ->paginate(10)
                ->withQueryString();
        }

        return view('admin.evaluasi.hasil.index', compact('sesiList', 'sesiTerpilih', 'hasilEvaluasi'));
    }

    public function show($sesiId, $dosenId)
    {
        $sesi = EvaluasiSesi::findOrFail($sesiId);
        $dosen = Dosen::findOrFail($dosenId);

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

        $masukanTeks = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
            ->where('dosen_id', $dosenId)
            ->whereNotNull('jawaban_teks')
            ->where('jawaban_teks', '!=', '')
            ->select('jawaban_teks', 'created_at')
            ->latest()
            ->get();

        $totalRataRata = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
            ->where('dosen_id', $dosenId)
            ->whereNotNull('jawaban_skala')
            ->avg('jawaban_skala');

        return view('admin.evaluasi.hasil.show', compact('sesi', 'dosen', 'detailPerPertanyaan', 'masukanTeks', 'totalRataRata'));
    }

    /**
     * [BARU] Fitur Cetak Laporan Evaluasi Dosen (PDF)
     */
    public function cetak($sesiId, $dosenId)
    {
        $sesi = EvaluasiSesi::findOrFail($sesiId);
        $dosen = Dosen::findOrFail($dosenId);

        // 1. Detail Skor
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

        // 2. Masukan Teks
        $masukanTeks = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
            ->where('dosen_id', $dosenId)
            ->whereNotNull('jawaban_teks')
            ->where('jawaban_teks', '!=', '')
            ->select('jawaban_teks', 'created_at')
            ->latest()
            ->get();

        // 3. Total Skor
        $totalRataRata = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
            ->where('dosen_id', $dosenId)
            ->whereNotNull('jawaban_skala')
            ->avg('jawaban_skala');

        // 4. Jumlah Responden
        $jumlahResponden = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiId)
            ->where('dosen_id', $dosenId)
            ->distinct('mahasiswa_id')
            ->count('mahasiswa_id');

        $pdf = Pdf::loadView('admin.evaluasi.hasil.cetak', compact('sesi', 'dosen', 'detailPerPertanyaan', 'masukanTeks', 'totalRataRata', 'jumlahResponden'));
        
        return $pdf->stream('Laporan_Evaluasi_' . $dosen->nidn . '.pdf');
    }
}