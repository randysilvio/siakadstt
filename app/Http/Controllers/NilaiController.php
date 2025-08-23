<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // <-- Pastikan Gate di-import

class NilaiController extends Controller
{
    /**
     * Menampilkan daftar mata kuliah untuk dipilih (HANYA UNTUK ADMIN).
     */
    public function index()
    {
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'AKSI TIDAK DIIZINKAN.');
        }
        $mata_kuliahs = MataKuliah::with('dosen')->get();
        return view('nilai.index', compact('mata_kuliahs'));
    }

    /**
     * Menampilkan form untuk input nilai (ADMIN, DOSEN PENGAMPU & KAPRODI).
     */
    public function show(MataKuliah $mataKuliah)
    {
        // Otorisasi menggunakan Gate yang sudah kita definisikan
        $this->authorize('inputNilai', $mataKuliah);

        $mataKuliah->load('mahasiswas');
        return view('nilai.show', compact('mataKuliah'));
    }

    /**
     * Menyimpan nilai yang diinput (ADMIN, DOSEN PENGAMPU & KAPRODI).
     */
    public function store(Request $request)
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'nilai.*' => 'nullable|string|max:2',
        ]);
    
        $mataKuliah = MataKuliah::find($request->mata_kuliah_id);

        // Otorisasi menggunakan Gate sebelum menyimpan
        $this->authorize('inputNilai', $mataKuliah);
        
        foreach ($request->nilai as $mahasiswa_id => $nilai) {
            $mataKuliah->mahasiswas()->updateExistingPivot($mahasiswa_id, ['nilai' => $nilai]);
        }

        $redirectRoute = Auth::user()->hasRole('admin') ? 'nilai.index' : 'dashboard';
        return redirect()->route($redirectRoute)->with('success', 'Nilai berhasil disimpan!');
    }
}
