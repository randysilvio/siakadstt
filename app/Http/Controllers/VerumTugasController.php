<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

use App\Support\BerkasPrivat;
use App\Models\VerumKelas;
use App\Models\VerumTugas;
use App\Models\VerumPengumpulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VerumTugasController extends Controller
{
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            abort(403, 'Aksi ini hanya untuk dosen pengampu kelas.');
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'instruksi' => 'required|string',
            'tenggat_waktu' => 'required|date',
        ]);

        $verum_kela->tugas()->create([
            'judul' => strip_tags($validated['judul']),
            'instruksi' => strip_tags($validated['instruksi']),
            'tenggat_waktu' => $validated['tenggat_waktu'],
        ]);

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function destroy(VerumTugas $verum_tuga): RedirectResponse
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_tuga->kelas)) {
            abort(403, 'Anda tidak berhak menghapus tugas ini.');
        }

        foreach ($verum_tuga->pengumpulan as $kumpul) {
            BerkasPrivat::hapus($kumpul->file_path);
        }
        
        $verum_tuga->delete();
        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    public function rekap(VerumTugas $verum_tuga): View
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_tuga->kelas)) {
            abort(403, 'Akses ditolak. Anda bukan pengampu kelas ini.');
        }

        $mahasiswas = $verum_tuga->kelas->pesertaQuery()
            ->orderBy('nama_lengkap')
            ->get();

        $pengumpulan = VerumPengumpulan::where('tugas_id', $verum_tuga->id)
                                       ->get()
                                       ->keyBy('mahasiswa_id');

        return view('verum.tugas_rekap', compact('verum_tuga', 'mahasiswas', 'pengumpulan'));
    }

    /**
     * [FITUR BARU OBE]: Menyimpan nilai tugas (0-100) & catatan umpan balik dosen pada pengumpulan tugas mahasiswa.
     */
    public function simpanNilai(Request $request, VerumTugas $verum_tuga): RedirectResponse
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_tuga->kelas)) {
            abort(403, 'Akses ditolak. Anda bukan pengampu kelas ini.');
        }

        $request->validate([
            'nilai' => 'nullable|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
            'catatan_dosen' => 'nullable|array',
            'catatan_dosen.*' => 'nullable|string|max:500',
        ]);

        $validMahasiswaIds = $verum_tuga->kelas->pesertaQuery()
            ->pluck('mahasiswas.id')
            ->toArray();

        DB::transaction(function () use ($request, $verum_tuga, $validMahasiswaIds) {
            $nilaiInput = $request->input('nilai', []);
            $catatanInput = $request->input('catatan_dosen', []);

            foreach ($nilaiInput as $mhsId => $skor) {
                if (!in_array((int) $mhsId, $validMahasiswaIds)) {
                    continue;
                }

                $catatan = isset($catatanInput[$mhsId]) ? strip_tags(trim($catatanInput[$mhsId])) : null;
                $skorClean = ($skor !== null && $skor !== '') ? round((float) $skor, 2) : null;

                $existing = VerumPengumpulan::where('tugas_id', $verum_tuga->id)
                    ->where('mahasiswa_id', $mhsId)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'nilai' => $skorClean,
                        'catatan_dosen' => $catatan,
                    ]);
                } elseif ($skorClean !== null || !empty($catatan)) {
                    // Memungkinkan dosen tetap memberi nilai meskipun tugas dikumpulkan luring/di luar file upload
                    VerumPengumpulan::create([
                        'tugas_id' => $verum_tuga->id,
                        'mahasiswa_id' => $mhsId,
                        'file_path' => '',
                        'waktu_pengumpulan' => now(),
                        'nilai' => $skorClean,
                        'catatan_dosen' => $catatan,
                    ]);
                }
            }
        });

        return back()->with('success', 'Nilai dan umpan balik tugas mahasiswa berhasil disimpan.');
    }

    public function storePengumpulan(Request $request, VerumTugas $verum_tuga): RedirectResponse
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa || !$verum_tuga->kelas) {
            abort(403, 'Aksi ini hanya untuk mahasiswa peserta kelas.');
        }

        // Peserta resmi: KRS disetujui pada semester kelas & rombel sesuai rombel kelas
        if (!$verum_tuga->kelas->isPeserta($mahasiswa)) {
            return back()->with('error', 'Anda tidak terdaftar sebagai peserta kelas/rombel ini.');
        }

        if (now()->isAfter($verum_tuga->tenggat_waktu)) {
            return back()->with('error', 'Gagal. Batas waktu pengumpulan tugas sudah ditutup.');
        }

        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,doc,docx,zip|max:10240',
        ], [
            'file_jawaban.required' => 'Anda harus memilih file untuk diunggah.',
            'file_jawaban.mimes' => 'Format file harus PDF, DOC, DOCX, atau ZIP.',
            'file_jawaban.max' => 'Ukuran file maksimal adalah 10MB.'
        ]);

        $existing = VerumPengumpulan::where('tugas_id', $verum_tuga->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        if ($existing) {
            BerkasPrivat::hapus($existing->file_path);
        }

        $filePath = BerkasPrivat::simpan($request->file('file_jawaban'), 'jawaban_tugas');

        VerumPengumpulan::updateOrCreate(
            [
                'tugas_id' => $verum_tuga->id,
                'mahasiswa_id' => $mahasiswa->id,
            ],
            [
                'file_path' => $filePath,
                'waktu_pengumpulan' => now(),
            ]
        );

        return back()->with('success', 'Jawaban tugas berhasil dikumpulkan.');
    }
}