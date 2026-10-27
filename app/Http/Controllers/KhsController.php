<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\TahunAkademik;
use Barryvdh\DomPDF\Facade\Pdf; 

class KhsController extends Controller
{
    /**
     * Menampilkan halaman KHS dengan filter semester
     */
    public function index(Request $request): View | RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->mahasiswa) {
            abort(403, 'Hanya mahasiswa yang dapat mengakses halaman ini.');
        }
        $mahasiswa = $user->mahasiswa;

        // Gatekeeper Terpusat: Cek Kewajiban Mengisi EDOM (Hanya untuk MK yang memiliki dosen pengampu)
        if ($mahasiswa->status_evaluasi === 'belum_isi') {
            return redirect()->route('evaluasi.index')
                ->with('error', 'PERHATIAN: Anda wajib menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM) untuk seluruh mata kuliah semester ini sebelum dapat melihat Kartu Hasil Studi (KHS).');
        }

        $riwayatTahunAkademikIds = $mahasiswa->mataKuliahs()
            ->whereNotNull('mahasiswa_mata_kuliah.tahun_akademik_id')
            ->distinct()
            ->pluck('mahasiswa_mata_kuliah.tahun_akademik_id');

        $riwayatTahunAkademiks = TahunAkademik::whereIn('id', $riwayatTahunAkademikIds)
            ->orderBy('tahun', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        $selectedTaId = $request->input('tahun_akademik_id');
        if (!$selectedTaId && $riwayatTahunAkademiks->isNotEmpty()) {
            $selectedTaId = $riwayatTahunAkademiks->first()->id;
        }

        $krsSelected = collect();
        $tahunSelected = null;
        $ipsData = ['total_sks' => 0, 'ips' => 0, 'nilaiBobot' => []];

        if ($selectedTaId) {
            $tahunSelected = TahunAkademik::find($selectedTaId);
            $krsSelected = $mahasiswa->mataKuliahs()
                ->wherePivot('tahun_akademik_id', $selectedTaId)
                ->withPivot('nilai', 'nama_kelas')
                ->get();
            $ipsData = $mahasiswa->hitungIps($selectedTaId);
        }

        return view('khs.index', compact(
            'mahasiswa',
            'riwayatTahunAkademiks',
            'krsSelected',
            'tahunSelected',
            'ipsData',
            'selectedTaId'
        ));
    }

    public function cetak(Request $request)
    {
        return app(CetakController::class)->cetakKhs($request);
    }
}