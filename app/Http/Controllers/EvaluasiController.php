<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiJawaban;
use App\Models\EvaluasiPertanyaan;
use App\Models\MataKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EvaluasiController extends Controller
{
    /**
     * Menampilkan daftar mata kuliah yang dapat dievaluasi oleh mahasiswa.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            abort(403, 'Hanya mahasiswa yang dapat mengakses halaman ini.');
        }

        $sesiAktif = EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->first();

        if (!$sesiAktif) {
            return view('evaluasi.tidak_ada_sesi');
        }

        $mataKuliah = collect();
        if ($mahasiswa->status_krs === 'Disetujui' && $sesiAktif->tahun_akademik_id) {
            $mataKuliah = $mahasiswa->mataKuliahs()
                ->wherePivot('tahun_akademik_id', $sesiAktif->tahun_akademik_id)
                ->with('dosen.user')
                ->get();
        }

        $evaluasiSelesai = EvaluasiJawaban::where('mahasiswa_id', $mahasiswa->id)
            ->where('evaluasi_sesi_id', $sesiAktif->id)
            ->distinct()
            ->pluck('mata_kuliah_id')
            ->toArray();

        return view('evaluasi.index', compact('sesiAktif', 'mataKuliah', 'evaluasiSelesai'));
    }

    /**
     * Menampilkan formulir kuesioner evaluasi untuk mata kuliah tertentu.
     */
    public function show(MataKuliah $mataKuliah): View | RedirectResponse
    {
        $sesiAktif = EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->firstOrFail();

        $pertanyaan = EvaluasiPertanyaan::where('is_active', true)->orderBy('urutan')->get();

        if (!$mataKuliah->dosen) {
            return redirect()->route('evaluasi.index')->with('error', 'Mata kuliah ini tidak memiliki dosen pengampu.');
        }

        return view('evaluasi.form', compact('sesiAktif', 'mataKuliah', 'pertanyaan'));
    }

    /**
     * Menyimpan hasil evaluasi dari formulir.
     */
    public function store(Request $request, MataKuliah $mataKuliah): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;
        $dosen = $mataKuliah->dosen;

        if (!$mahasiswa || !$dosen) {
            abort(403, 'Data mahasiswa atau dosen tidak valid.');
        }

        $request->validate([
            'sesi_id' => 'required|exists:evaluasi_sesi,id',
            'jawaban' => 'required|array',
            'jawaban.*' => 'required',
        ]);

        $sesiId = $request->input('sesi_id');

        DB::transaction(function () use ($request, $mahasiswa, $dosen, $mataKuliah, $sesiId) {
            foreach ($request->input('jawaban', []) as $pertanyaanId => $jawaban) {
                $pertanyaan = EvaluasiPertanyaan::find($pertanyaanId);
                if ($pertanyaan) {
                    EvaluasiJawaban::updateOrCreate(
                        [
                            'evaluasi_sesi_id' => $sesiId,
                            'mahasiswa_id' => $mahasiswa->id,
                            'dosen_id' => $dosen->id,
                            'mata_kuliah_id' => $mataKuliah->id,
                            'evaluasi_pertanyaan_id' => $pertanyaanId,
                        ],
                        [
                            'jawaban_skala' => ($pertanyaan->tipe_jawaban == 'skala_1_5') ? $jawaban : null,
                            'jawaban_teks' => ($pertanyaan->tipe_jawaban == 'teks') ? $jawaban : null,
                        ]
                    );
                }
            }
        });

        return redirect()->route('evaluasi.index')->with('success', 'Terima kasih! Evaluasi untuk mata kuliah ' . $mataKuliah->nama_mk . ' telah berhasil disimpan.');
    }
}