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
                ->with(['dosen.user', 'teamDosens'])
                ->get();
        }

        $evaluasiSelesai = EvaluasiJawaban::where('mahasiswa_id', $mahasiswa->id)
            ->where('evaluasi_sesi_id', $sesiAktif->id)
            ->distinct()
            ->pluck('mata_kuliah_id')
            ->toArray();

        return view('evaluasi.index', compact('sesiAktif', 'mataKuliah', 'evaluasiSelesai'));
    }

    public function show(MataKuliah $mataKuliah): View | RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa || $mahasiswa->status_krs !== 'Disetujui') {
            return redirect()->route('evaluasi.index')->with('error', 'Kuesioner hanya dapat diakses jika KRS Anda telah disetujui.');
        }

        $sesiAktif = EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->firstOrFail();

        $terdaftarDiKrs = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $sesiAktif->tahun_akademik_id)
            ->where('mata_kuliahs.id', $mataKuliah->id)
            ->exists();

        if (!$terdaftarDiKrs) {
            return redirect()->route('evaluasi.index')->with('error', 'Akses ditolak. Anda tidak mengambil mata kuliah ini pada semester evaluasi aktif.');
        }

        $dosenTarget = $mataKuliah->dosen ?? $mataKuliah->teamDosens()->first();
        if (!$dosenTarget) {
            return redirect()->route('evaluasi.index')->with('error', 'Mata kuliah ini belum memiliki dosen pengampu.');
        }

        $pertanyaan = EvaluasiPertanyaan::where('is_active', true)->orderBy('urutan')->get();

        $jawabanTersimpan = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiAktif->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->get()
            ->keyBy('evaluasi_pertanyaan_id');

        return view('evaluasi.form', compact('sesiAktif', 'mataKuliah', 'pertanyaan', 'jawabanTersimpan'));
    }

    public function store(Request $request, MataKuliah $mataKuliah): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;
        $dosen = $mataKuliah->dosen ?? $mataKuliah->teamDosens()->first();

        if (!$mahasiswa || !$dosen || $mahasiswa->status_krs !== 'Disetujui') {
            abort(403, 'Data mahasiswa, status KRS, atau dosen pengampu tidak valid.');
        }

        $sesiAktif = EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->firstOrFail();

        $terdaftarDiKrs = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $sesiAktif->tahun_akademik_id)
            ->where('mata_kuliahs.id', $mataKuliah->id)
            ->exists();

        if (!$terdaftarDiKrs) {
            abort(403, 'Akses ditolak. Anda tidak terdaftar pada mata kuliah ini.');
        }

        $pertanyaanAktif = EvaluasiPertanyaan::where('is_active', true)->orderBy('urutan')->get();

        $request->validate([
            'jawaban' => 'required|array',
        ]);

        $inputJawaban = $request->input('jawaban', []);
        foreach ($pertanyaanAktif as $p) {
            if (!isset($inputJawaban[$p->id]) || trim((string) $inputJawaban[$p->id]) === '') {
                return back()->withInput()->with('error', 'Harap mengisi seluruh butir pertanyaan evaluasi sebelum menyimpan.');
            }
        }

        DB::transaction(function () use ($pertanyaanAktif, $inputJawaban, $mahasiswa, $dosen, $mataKuliah, $sesiAktif) {
            foreach ($pertanyaanAktif as $pertanyaan) {
                $jawaban = $inputJawaban[$pertanyaan->id];

                $nilaiSkala = null;
                if ($pertanyaan->tipe_jawaban === 'skala_1_5') {
                    $val = (int) $jawaban;
                    $val = max(1, min(4, $val));
                    $nilaiSkala = $val;
                }

                EvaluasiJawaban::updateOrCreate(
                    [
                        'evaluasi_sesi_id'       => $sesiAktif->id,
                        'mahasiswa_id'           => $mahasiswa->id,
                        'dosen_id'               => $dosen->id,
                        'mata_kuliah_id'         => $mataKuliah->id,
                        'evaluasi_pertanyaan_id' => $pertanyaan->id,
                    ],
                    [
                        'jawaban_skala' => $nilaiSkala,
                        'jawaban_teks'  => ($pertanyaan->tipe_jawaban === 'teks') ? strip_tags(trim((string) $jawaban)) : null,
                    ]
                );
            }
        });

        return redirect()->route('evaluasi.index')->with('success', 'Terima kasih! Evaluasi untuk mata kuliah ' . $mataKuliah->nama_mk . ' telah berhasil disimpan.');
    }
}