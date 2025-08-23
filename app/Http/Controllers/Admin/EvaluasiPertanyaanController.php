<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiPertanyaan;
use Illuminate\Http\Request;

class EvaluasiPertanyaanController extends Controller
{
    public function index()
    {
        $pertanyaan = EvaluasiPertanyaan::orderBy('urutan')->paginate(10);
        return view('admin.evaluasi.pertanyaan.index', compact('pertanyaan'));
    }

    public function create()
    {
        return view('admin.evaluasi.pertanyaan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan' => 'required|string',
            'tipe_jawaban' => 'required|in:skala_1_5,teks',
            'urutan' => 'required|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        EvaluasiPertanyaan::create([
            'pertanyaan' => $request->pertanyaan,
            'tipe_jawaban' => $request->tipe_jawaban,
            'urutan' => $request->urutan ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('evaluasi-pertanyaan.index')->with('success', 'Pertanyaan evaluasi berhasil dibuat.');
    }

    public function edit(EvaluasiPertanyaan $evaluasi_pertanyaan)
    {
        return view('admin.evaluasi.pertanyaan.edit', ['pertanyaan' => $evaluasi_pertanyaan]);
    }

    public function update(Request $request, EvaluasiPertanyaan $evaluasi_pertanyaan)
    {
        $request->validate([
            'pertanyaan' => 'required|string',
            'tipe_jawaban' => 'required|in:skala_1_5,teks',
            'urutan' => 'required|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $evaluasi_pertanyaan->update([
            'pertanyaan' => $request->pertanyaan,
            'tipe_jawaban' => $request->tipe_jawaban,
            'urutan' => $request->urutan ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('evaluasi-pertanyaan.index')->with('success', 'Pertanyaan evaluasi berhasil diperbarui.');
    }

    public function destroy(EvaluasiPertanyaan $evaluasi_pertanyaan)
    {
        // Anda bisa menambahkan pengecekan di sini jika pertanyaan sudah pernah digunakan
        $evaluasi_pertanyaan->delete();

        return redirect()->route('evaluasi-pertanyaan.index')->with('success', 'Pertanyaan evaluasi berhasil dihapus.');
    }
}