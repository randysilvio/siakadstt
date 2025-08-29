<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumTugas;
use App\Models\VerumPengumpulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class VerumTugasController extends Controller
{
    /**
     * Menyimpan tugas baru yang dibuat oleh dosen.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        $this->authorize('update', $verum_kela);

        $request->validate([
            'judul' => 'required|string|max:255',
            'instruksi' => 'required|string',
            'tenggat_waktu' => 'required|date',
        ]);

        $verum_kela->tugas()->create($request->all());

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    /**
     * Menyimpan file jawaban yang diunggah oleh mahasiswa.
     */
    public function storePengumpulan(Request $request, VerumTugas $verum_tuga): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            abort(403, 'Aksi ini hanya untuk mahasiswa.');
        }

        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,doc,docx,zip|max:10240',
        ]);

        $filePath = $request->file('file_jawaban')->store('jawaban_tugas', 'public');

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