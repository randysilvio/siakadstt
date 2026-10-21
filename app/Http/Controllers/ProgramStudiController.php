<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProgramStudiController extends Controller
{
    public function index(): View
    {
        $program_studis = ProgramStudi::with('kaprodi.user')->orderBy('kode_prodi', 'asc')->orderBy('nama_prodi', 'asc')->get();
        return view('program-studi.index', compact('program_studis'));
    }

    public function create(): View
    {
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        return view('program-studi.create', compact('dosens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_prodi' => 'required|string|max:10|unique:program_studis,kode_prodi',
            'nama_prodi' => 'required|string|max:255|unique:program_studis,nama_prodi',
            'kaprodi_dosen_id' => 'nullable|exists:dosens,id',
        ]);

        $validated['kode_prodi'] = strtoupper(trim($validated['kode_prodi']));
        ProgramStudi::create($validated);

        return redirect()->route('admin.program-studi.index')->with('success', 'Program Studi berhasil ditambahkan!');
    }

    public function edit(ProgramStudi $programStudi): View
    {
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        return view('program-studi.edit', compact('programStudi', 'dosens'));
    }

    public function update(Request $request, ProgramStudi $programStudi): RedirectResponse
    {
        $validated = $request->validate([
            'kode_prodi' => 'required|string|max:10|unique:program_studis,kode_prodi,' . $programStudi->id,
            'nama_prodi' => 'required|string|max:255|unique:program_studis,nama_prodi,' . $programStudi->id,
            'kaprodi_dosen_id' => 'nullable|exists:dosens,id',
        ]);

        $validated['kode_prodi'] = strtoupper(trim($validated['kode_prodi']));
        $programStudi->update($validated);

        return redirect()->route('admin.program-studi.index')->with('success', 'Program Studi berhasil diperbarui!');
    }

    public function destroy(ProgramStudi $programStudi): RedirectResponse
    {
        if ($programStudi->mahasiswas()->exists()) {
            return redirect()->route('admin.program-studi.index')->with('error', 'Program Studi tidak bisa dihapus karena masih memiliki mahasiswa terdaftar.');
        }
        if ($programStudi->dosens()->exists()) {
            return redirect()->route('admin.program-studi.index')->with('error', 'Program Studi tidak bisa dihapus karena masih memiliki dosen homebase terdaftar.');
        }

        $programStudi->delete();
        return redirect()->route('admin.program-studi.index')->with('success', 'Program Studi berhasil dihapus!');
    }
}