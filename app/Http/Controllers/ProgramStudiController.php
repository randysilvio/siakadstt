<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProgramStudiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Eager load relasi kaprodi dan user untuk menampilkan nama
        $program_studis = ProgramStudi::with('kaprodi.user')->latest()->get();
        return view('program-studi.index', compact('program_studis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('program-studi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_prodi' => 'required|unique:program_studis|max:255',
        ]);
        ProgramStudi::create($request->all());
        return redirect()->route('admin.program-studi.index')->with('success', 'Program Studi berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProgramStudi $programStudi): View
    {
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        return view('program-studi.edit', compact('programStudi', 'dosens'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProgramStudi $programStudi): RedirectResponse
    {
        $request->validate([
            'nama_prodi' => 'required|max:255|unique:program_studis,nama_prodi,' . $programStudi->id,
            'kaprodi_dosen_id' => 'nullable|exists:dosens,id'
        ]);
        $programStudi->update($request->all());
        return redirect()->route('admin.program-studi.index')->with('success', 'Program Studi berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgramStudi $programStudi): RedirectResponse
    {
        if ($programStudi->mahasiswas()->exists()) {
            return redirect()->route('admin.program-studi.index')->with('error', 'Program Studi tidak bisa dihapus karena masih memiliki mahasiswa terdaftar.');
        }
        $programStudi->delete();
        return redirect()->route('admin.program-studi.index')->with('success', 'Program Studi berhasil dihapus!');
    }
}

