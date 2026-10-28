<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

use App\Support\BerkasPrivat;
use App\Models\VerumKelas;
use App\Models\VerumTugas;
use App\Models\VerumPengumpulan;
use App\Services\VerumService;
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

        app(VerumService::class)->simpanTugas($verum_kela, $request->validate(VerumService::ATURAN_TUGAS));

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function destroy(VerumTugas $verum_tuga): RedirectResponse
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_tuga->kelas)) {
            abort(403, 'Anda tidak berhak menghapus tugas ini.');
        }

        app(VerumService::class)->hapusTugas($verum_tuga);
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

        $request->validate(VerumService::ATURAN_NILAI);
        app(VerumService::class)->simpanNilai($verum_tuga, $request->input('nilai', []), $request->input('catatan_dosen', []));

        return back()->with('success', 'Nilai dan umpan balik tugas mahasiswa berhasil disimpan.');
    }

    public function storePengumpulan(Request $request, VerumTugas $verum_tuga): RedirectResponse
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa || !$verum_tuga->kelas) {
            abort(403, 'Aksi ini hanya untuk mahasiswa peserta kelas.');
        }

        $request->validate(VerumService::ATURAN_JAWABAN, VerumService::PESAN_JAWABAN);

        $error = app(VerumService::class)->kumpulkan($verum_tuga, $mahasiswa, $request->file('file_jawaban'));
        if ($error) {
            return back()->with('error', $error);
        }

        return back()->with('success', 'Jawaban tugas berhasil dikumpulkan.');
    }
}