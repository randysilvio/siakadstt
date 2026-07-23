<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\TahunAkademik;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiJawaban;

class KhsController extends Controller
{
    public function index(): View | RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->mahasiswa) {
            abort(403, 'Hanya mahasiswa yang dapat mengakses halaman ini.');
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
                        ->with('error', 'PERHATIAN: Anda wajib menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM) untuk seluruh mata kuliah semester ini sebelum dapat melihat Kartu Hasil Studi (KHS).');
                }
            }
        }
        // ------------------------------------------------

        // [PERBAIKAN LOGIKA 1]: Filter spesifik per Tahun Akademik (KHS)
        // Ambil data KRS, dan pastikan kita mengelompokkannya secara spesifik per ID Tahun Akademik 
        // yang ada di tabel pivot, sehingga nilai yang diulang tahun depan tidak masuk ke KHS tahun lalu.
        $krsPerTahunAkademik = $mahasiswa->mataKuliahs()
            ->withPivot('nilai', 'tahun_akademik_id')
            ->get()
            ->groupBy('pivot.tahun_akademik_id');

        $tahunAkademiks = TahunAkademik::find($krsPerTahunAkademik->keys());

        return view('khs.index', compact(
            'mahasiswa',
            'krsPerTahunAkademik',
            'tahunAkademiks'
        ));
    }
}