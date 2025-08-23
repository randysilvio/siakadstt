<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiJawaban;
use App\Models\EvaluasiPertanyaan;
use App\Models\MataKuliah;
use Illuminate\Support\Facades\DB;

class EvaluasiController extends Controller
{
    /**
     * Menampilkan daftar mata kuliah yang dapat dievaluasi oleh mahasiswa.
     */
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;

        // Cari sesi evaluasi yang sedang aktif
        $sesiAktif = EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->first();

        // Jika tidak ada sesi aktif, tampilkan pesan
        if (!$sesiAktif) {
            return view('evaluasi.tidak_ada_sesi');
        }

        // Ambil daftar mata kuliah dari KRS mahasiswa pada tahun akademik sesi aktif
        $mataKuliah = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $sesiAktif->tahun_akademik_id)
            ->with('dosen')
            ->get();

        // Cek mata kuliah mana yang sudah diisi evaluasinya oleh mahasiswa
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
    public function show(MataKuliah $mataKuliah)
    {
        // Cari sesi evaluasi yang aktif
        $sesiAktif = EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->firstOrFail(); // Gagal jika tidak ada sesi

        // Ambil semua pertanyaan yang aktif dan urutkan
        $pertanyaan = EvaluasiPertanyaan::where('is_active', true)->orderBy('urutan')->get();

        // Pastikan dosen pengampu ada
        if (!$mataKuliah->dosen) {
            return redirect()->route('evaluasi.index')->with('error', 'Mata kuliah ini tidak memiliki dosen pengampu.');
        }

        return view('evaluasi.form', compact('sesiAktif', 'mataKuliah', 'pertanyaan'));
    }

    /**
     * Menyimpan hasil evaluasi dari formulir.
     */
    public function store(Request $request, MataKuliah $mataKuliah)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $dosen = $mataKuliah->dosen;

        // Validasi dasar
        $request->validate([
            'sesi_id' => 'required|exists:evaluasi_sesi,id',
            'jawaban' => 'required|array',
        ]);

        $sesiId = $request->input('sesi_id');

        // Gunakan transaction untuk memastikan semua data tersimpan atau tidak sama sekali
        DB::transaction(function () use ($request, $mahasiswa, $dosen, $mataKuliah, $sesiId) {
            foreach ($request->input('jawaban') as $pertanyaanId => $jawaban) {
                
                // Ambil detail pertanyaan untuk mengetahui tipenya
                $pertanyaan = EvaluasiPertanyaan::findOrFail($pertanyaanId);

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
        });

        return redirect()->route('evaluasi.index')->with('success', 'Terima kasih! Evaluasi untuk mata kuliah ' . $mataKuliah->nama_mk . ' telah berhasil disimpan.');
    }
}