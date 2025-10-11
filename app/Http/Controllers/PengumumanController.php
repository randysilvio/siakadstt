<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth; // <-- Perubahan 1: Mengimpor Auth facade

class PengumumanController extends Controller
{
    public function index(): View
    {
        $pengumumans = Pengumuman::latest()->paginate(10);
        return view('pengumuman.index', compact('pengumumans'));
    }

    public function create(): View
    {
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
        // =======================================================================================
        // ===== PERBAIKAN: Menambahkan logika otorisasi untuk memvalidasi hak akses pengguna =====
        // =======================================================================================
        $user = Auth::user();

        // Izinkan akses jika:
        // 1. Pengumuman ditujukan untuk 'semua'.
        // 2. ATAU pengguna memiliki peran yang sesuai dengan target pengumuman.
        if ($pengumuman->target_role === 'semua' || $user->hasRole($pengumuman->target_role)) {
            return view('pengumuman.show', compact('pengumuman'));
        }

        // Jika tidak memenuhi syarat, tolak akses dengan pesan error 403 (Forbidden).
        abort(403, 'ANDA TIDAK MEMILIKI WEWENANG UNTUK MELIHAT PENGUMUMAN INI.');
    }

    public function edit(Pengumuman $pengumuman): View
    {
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