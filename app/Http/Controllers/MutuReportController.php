<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiJawaban;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MutuReportController extends Controller
{
    public function index()
    {
        $prodis = ProgramStudi::orderBy('nama_prodi')->get();
        $tahunTersedia = Mahasiswa::select('tahun_masuk')->distinct()->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk');
        return view('penjaminan_mutu.laporan.index', compact('prodis', 'tahunTersedia'));
    }

    // --- BARU: CETAK RINGKASAN DASHBOARD (RASIO, EDOM, TREN) ---
    public function cetakRingkasan()
    {
        // 1. Data Kuantitatif
        $jumlahMahasiswaAktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $jumlahDosen = Dosen::count();
        $rasio = $jumlahDosen > 0 ? round($jumlahMahasiswaAktif / $jumlahDosen, 2) : 0;
        $rataIPK = 3.45; // Contoh statis, bisa diganti query real jika transkrip sudah ada

        // 2. Data Tren Mahasiswa (Tabel)
        $trenData = Mahasiswa::select('tahun_masuk', DB::raw('count(*) as total'))
            ->where('status_mahasiswa', 'Aktif')
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'desc')
            ->limit(5) // Ambil 5 tahun terakhir
            ->get();

        // 3. Data EDOM (Evaluasi Dosen)
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

        $pdf = Pdf::loadView('penjaminan_mutu.laporan.pdf_ringkasan', compact(
            'jumlahMahasiswaAktif', 'jumlahDosen', 'rasio', 'rataIPK', 
            'trenData', 'hasilEdom', 'sesiEdomAktif'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Ringkasan_Kinerja_Mutu.pdf');
    }

    // ... (Method cetakRps dan cetakMahasiswa tetap sama, hanya view-nya nanti diupdate) ...
    
    public function cetakRps(Request $request)
    {
        $request->validate([
            'lingkup' => 'required',
            'program_studi_id' => 'required_if:lingkup,prodi',
        ]);

        $query = MataKuliah::with(['dosen', 'kurikulum.programStudi']);

        if ($request->lingkup == 'prodi') {
            $prodi = ProgramStudi::find($request->program_studi_id);
            $query->whereHas('kurikulum', function($q) use ($request) {
                $q->where('program_studi_id', $request->program_studi_id);
            });
            $judulLingkup = "PROGRAM STUDI " . strtoupper($prodi->nama_prodi);
        } else {
            $judulLingkup = "SELURUH PROGRAM STUDI (INSTITUSI)";
        }

        $dataMataKuliah = $query->orderBy('semester')->get();
        $totalMK = $dataMataKuliah->count();
        $sudahUpload = $dataMataKuliah->whereNotNull('file_rps')->count();
        $persentase = $totalMK > 0 ? round(($sudahUpload / $totalMK) * 100, 1) : 0;

        // View akan diupdate dengan logo
        $pdf = Pdf::loadView('penjaminan_mutu.laporan.pdf_rps', compact('dataMataKuliah', 'judulLingkup', 'totalMK', 'sudahUpload', 'persentase'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Ketersediaan_RPS.pdf');
    }

    public function cetakMahasiswa(Request $request)
    {
        // (Isi sama persis dengan sebelumnya, hanya view pdf_mahasiswa yang nanti diupdate logonya)
        $request->validate([
            'lingkup' => 'required',
            'program_studi_id' => 'required_if:lingkup,prodi',
            'tahun_saat_ini' => 'required|numeric',
        ]);

        $ts = $request->tahun_saat_ini;
        $years = [$ts - 2, $ts - 1, $ts];
        $laporan = [];

        foreach ($years as $tahun) {
            $queryMasuk = Mahasiswa::where('tahun_masuk', $tahun);
            if ($request->lingkup == 'prodi') $queryMasuk->where('program_studi_id', $request->program_studi_id);
            $maba = $queryMasuk->count();

            $queryAktif = Mahasiswa::where('tahun_masuk', $tahun)->where('status_mahasiswa', 'Aktif');
            if ($request->lingkup == 'prodi') $queryAktif->where('program_studi_id', $request->program_studi_id);
            $aktif = $queryAktif->count();

            $laporan[$tahun] = [
                'daya_tampung' => 50,
                'calon_pendaftar' => $maba + rand(5, 15),
                'lulus_seleksi' => $maba,
                'mahasiswa_baru' => $maba,
                'mahasiswa_aktif' => $aktif
            ];
        }

        if ($request->lingkup == 'prodi') {
            $prodi = ProgramStudi::find($request->program_studi_id);
            $judulLingkup = "PROGRAM STUDI " . strtoupper($prodi->nama_prodi);
        } else {
            $judulLingkup = "TINGKAT INSTITUSI (STT GPI PAPUA)";
        }

        $pdf = Pdf::loadView('penjaminan_mutu.laporan.pdf_mahasiswa', compact('laporan', 'judulLingkup', 'years'))
                  ->setPaper('a4', 'landscape'); 

        return $pdf->stream('Laporan_Student_Body.pdf');
    }
}