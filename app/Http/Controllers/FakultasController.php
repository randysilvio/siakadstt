<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FakultasController extends Controller
{
    public function index(): View
    {
        $fakultas = Fakultas::with(['dekan', 'programStudis'])->orderBy('kode_fakultas')->get();
        $dosens = Dosen::orderBy('nama_lengkap')->get();

        return view('fakultas.index', compact('fakultas', 'dosens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_fakultas'  => 'required|string|max:15|unique:fakultas,kode_fakultas',
            'nama_fakultas'  => 'required|string|max:255',
            'dekan_dosen_id' => 'nullable|exists:dosens,id',
        ]);

        $validated['kode_fakultas'] = strtoupper(trim($validated['kode_fakultas']));
        Fakultas::create($validated);

        return redirect()->route('admin.fakultas.index')->with('success', 'Data fakultas berhasil ditambahkan.');
    }

    public function update(Request $request, Fakultas $fakulta): RedirectResponse
    {
        $validated = $request->validate([
            'kode_fakultas'  => 'required|string|max:15|unique:fakultas,kode_fakultas,' . $fakulta->id,
            'nama_fakultas'  => 'required|string|max:255',
            'dekan_dosen_id' => 'nullable|exists:dosens,id',
        ]);

        $validated['kode_fakultas'] = strtoupper(trim($validated['kode_fakultas']));
        $fakulta->update($validated);

        return redirect()->route('admin.fakultas.index')->with('success', 'Data fakultas berhasil diperbarui.');
    }

    public function destroy(Fakultas $fakulta): RedirectResponse
    {
        if ($fakulta->programStudis()->exists()) {
            return redirect()->route('admin.fakultas.index')
                ->with('error', 'Fakultas tidak dapat dihapus karena masih menaungi Program Studi.');
        }

        $fakulta->delete();
        return redirect()->route('admin.fakultas.index')->with('success', 'Data fakultas berhasil dihapus.');
    }
}