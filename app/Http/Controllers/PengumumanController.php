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
        $pengumumans = Pengumuman::with('roles')->latest()->paginate(10);
        return view('pengumuman.index', compact('pengumumans'));
    }

    public function create(): View
    {
        $roles = Role::where('name', '!=', 'admin')->get();
        return view('pengumuman.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'target_roles' => 'required|array',
            'target_roles.*' => 'exists:roles,id',
        ]);

        DB::transaction(function () use ($request) {
            $pengumuman = Pengumuman::create($request->only('judul', 'konten'));
            $pengumuman->roles()->sync($request->target_roles);
        });

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function show(Pengumuman $pengumuman): View
    {
        return view('pengumuman.show', compact('pengumuman'));
    }

    public function edit(Pengumuman $pengumuman): View
    {
        $roles = Role::where('name', '!=', 'admin')->get();
        $pengumuman->load('roles');
        return view('pengumuman.edit', compact('pengumuman', 'roles'));
    }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'target_roles' => 'required|array',
            'target_roles.*' => 'exists:roles,id',
        ]);

        DB::transaction(function () use ($request, $pengumuman) {
            $pengumuman->update($request->only('judul', 'konten'));
            $pengumuman->roles()->sync($request->target_roles);
        });

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $pengumuman->delete();
        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
