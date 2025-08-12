<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumTugas;
use App\Models\VerumPengumpulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerumTugasController extends Controller
{
    /**
     * Menyimpan tugas baru yang dibuat oleh dosen.
     */
    public function store(Request $request, VerumKelas $verum_kela)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'instruksi' => 'required|string',
            'tenggat_waktu' => 'required|date',
        ]);

        VerumTugas::create([
            'kelas_id' => $verum_kela->id,
            'judul' => $request->judul,
            'instruksi' => $request->instruksi,
            'tenggat_waktu' => $request->tenggat_waktu,
        ]);

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    /**
     * Menyimpan file jawaban yang diunggah oleh mahasiswa.
     */
    public function storePengumpulan(Request $request, VerumTugas $verum_tuga)
    {
        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,doc,docx,zip|max:10240', // Maks 10MB
        ]);

        $mahasiswaId = Auth::user()->mahasiswa->id;

        // Simpan file ke storage/app/public/jawaban_tugas
        $filePath = $request->file('file_jawaban')->store('public/jawaban_tugas');

        // Gunakan updateOrCreate untuk menangani jika mahasiswa mengunggah ulang
        VerumPengumpulan::updateOrCreate(
            [
                'tugas_id' => $verum_tuga->id,
                'mahasiswa_id' => $mahasiswaId,
            ],
            [
                'file_path' => $filePath,
                'waktu_pengumpulan' => now(),
            ]
        );

        return back()->with('success', 'Jawaban tugas berhasil dikumpulkan.');
    }
}
