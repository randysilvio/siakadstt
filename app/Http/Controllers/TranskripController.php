<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiJawaban;

class TranskripController extends Controller
{
    public function index(): View | RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa;

        // --- INTERCEPTOR: Cek Kewajiban Mengisi EDOM ---
        $sesiAktif = EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->first();

        if ($sesiAktif && $mahasiswa->status_krs === 'Disetujui' && $sesiAktif->tahun_akademik_id) {
            $mataKuliahWajibEdom = $mahasiswa->mataKuliahs()
                ->wherePivot('tahun_akademik_id', $sesiAktif->tahun_akademik_id)
                ->pluck('mata_kuliahs.id')
                ->toArray();

            if (!empty($mataKuliahWajibEdom)) {
                $evaluasiSelesai = EvaluasiJawaban::where('mahasiswa_id', $mahasiswa->id)
                    ->where('evaluasi_sesi_id', $sesiAktif->id)
                    ->distinct()
                    ->pluck('mata_kuliah_id')
                    ->toArray();

                $belumDievaluasi = array_diff($mataKuliahWajibEdom, $evaluasiSelesai);

                if (!empty($belumDievaluasi)) {
                    return redirect()->route('evaluasi.index')
                        ->with('error', 'PERHATIAN: Anda wajib menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM) semester ini sebelum dapat mengakses halaman Transkrip Nilai.');
                }
            }
        }
        // ------------------------------------------------

        // Ambil semua mata kuliah yang sudah dinilai
        $krs = $mahasiswa->mataKuliahs()
                         ->wherePivotNotNull('nilai')
                         ->get();

        // Kelompokkan mata kuliah berdasarkan semester dari tabel mata_kuliahs
        $krs_per_semester = $krs->groupBy('semester');

        // Gunakan method dari model untuk menghitung IPK
        $ipk = $mahasiswa->hitungIpk();
        $total_sks = $mahasiswa->totalSksLulus();


        return view('transkrip.index', compact('mahasiswa', 'krs_per_semester', 'total_sks', 'ipk'));
    }
}