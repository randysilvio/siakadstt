<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Notifications\KrsValidated;

class ValidasiKrsController extends Controller
{
    /**
     * Menampilkan detail KRS seorang mahasiswa untuk divalidasi oleh Kaprodi.
     */
    public function show(Mahasiswa $mahasiswa): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // [PERBAIKAN FASE 2]: Mendukung Kaprodi eksplisit (hasRole) maupun implisit (isKaprodi)
        if (!$user || !$user->dosen || (!$user->hasRole('kaprodi') && !$user->isKaprodi())) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->firstOrFail();

        // Keamanan: Pastikan Kaprodi hanya bisa memvalidasi mahasiswa di prodinya sendiri
        if ((int) $mahasiswa->program_studi_id !== (int) $programStudi->id) {
            abort(403, 'Anda hanya dapat memvalidasi mahasiswa dari program studi Anda.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();
        
        $mahasiswa->load(['mataKuliahs' => function($query) use ($periodeAktif) {
            $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeAktif->id)
                  ->with('jadwals');
        }]);
        
        return view('kaprodi.validasi_krs', compact('mahasiswa'));
    }

    /**
     * Memproses aksi validasi (setujui/tolak).
     */
    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'status_krs' => 'required|in:Disetujui,Ditolak',
            'catatan_kaprodi' => 'nullable|string|max:500',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // [PERBAIKAN FASE 2]: Otorisasi konsisten & penutupan celah IDOR lintas Program Studi
        if (!$user || !$user->dosen || (!$user->hasRole('kaprodi') && !$user->isKaprodi())) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->firstOrFail();

        if ((int) $mahasiswa->program_studi_id !== (int) $programStudi->id) {
            abort(403, 'Akses ditolak. Mahasiswa ini bukan berasal dari program studi Anda.');
        }

        $mahasiswa->update([
            'status_krs' => $request->status_krs,
            'catatan_kaprodi' => $request->catatan_kaprodi,
        ]);

        if ($mahasiswa->user) {
            $mahasiswa->user->notify(new KrsValidated($request->status_krs, $request->catatan_kaprodi));
        }

        return redirect()->route('kaprodi.dashboard')->with('success', 'KRS Mahasiswa berhasil di' . strtolower($request->status_krs) . '.');
    }
}