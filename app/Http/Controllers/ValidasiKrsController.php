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
     * Menampilkan detail KRS seorang mahasiswa untuk divalidasi.
     */
    public function show(Mahasiswa $mahasiswa): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Otorisasi: Pastikan user adalah Kaprodi
        if (!$user->hasRole('kaprodi') || !$user->dosen) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->firstOrFail();

        // Keamanan: pastikan kaprodi hanya bisa validasi mahasiswa di prodinya
        if ($mahasiswa->program_studi_id != $programStudi->id) {
            abort(403, 'Anda hanya dapat memvalidasi mahasiswa dari program studi Anda.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();
        
        // [PERBAIKAN] Hanya me-load KRS dari semester aktif
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

        // Otorisasi tambahan sebelum update
        if (!$user->hasRole('kaprodi') || !$user->dosen) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        // Update status KRS mahasiswa
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