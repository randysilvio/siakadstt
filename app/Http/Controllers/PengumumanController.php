<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    public function index(): View
    {
        $pengumumans = Pengumuman::latest()->paginate(10);
        // PERBAIKAN: Mengarahkan ke view 'pengumuman.index'
        return view('pengumuman.index', compact('pengumumans'));
    }

    public function create(): View
    {
        // PERBAIKAN: Mengarahkan ke view 'pengumuman.create'
        return view('pengumuman.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'target_role' => 'required|string|in:semua,admin,dosen,mahasiswa,tendik',
        ]);

        Pengumuman::create($request->only('judul', 'konten', 'target_role'));

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function show(Pengumuman $pengumuman): View
    {
        // PERBAIKAN: Mengarahkan ke view 'pengumuman.show'
        return view('pengumuman.show', compact('pengumuman'));
    }

    public function edit(Pengumuman $pengumuman): View
    {
        // PERBAIKAN: Mengarahkan ke view 'pengumuman.edit'
        return view('pengumuman.edit', compact('pengumuman'));
    }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'target_role' => 'required|string|in:semua,admin,dosen,mahasiswa',
        ]);

        $pengumuman->update($request->only('judul', 'konten', 'target_role'));

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $pengumuman->delete();
        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}