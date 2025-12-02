<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengumumanController extends Controller
{
    /**
     * Menampilkan daftar pengumuman dengan Smart Filter.
     */
    public function index(Request $request): View
    {
        $query = Pengumuman::latest();

        // 1. Filter Pencarian Teks (Judul)
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->input('search') . '%');
        }

        // 2. Filter Kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        // 3. Filter Target Role
        if ($request->filled('target_role')) {
            $query->where('target_role', $request->input('target_role'));
        }

        $pengumumans = $query->paginate(10)->withQueryString();
        
        return view('pengumuman.index', compact('pengumumans'));
    }

    // ... (SISA METHOD LAIN: create, store, show, edit, update, destroy TETAP SAMA) ...
    // Pastikan Anda menggunakan method store/update/destroy dari update sebelumnya 
    // yang sudah mendukung fitur upload foto dan kategori.
    
    public function create(): View { return view('pengumuman.create'); }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|in:berita,pengumuman',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'konten' => 'required|string',
            'target_role' => 'required|string|in:semua,admin,dosen,mahasiswa,tendik',
        ]);
        $data = $request->only('judul', 'kategori', 'konten', 'target_role');
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('pengumuman', 'public');
        }
        Pengumuman::create($data);
        return redirect()->route('admin.pengumuman.index')->with('success', 'Data berhasil dibuat.');
    }

    public function show(Pengumuman $pengumuman): View
    {
        $user = Auth::user();
        if ($pengumuman->target_role === 'semua' || $user->hasRole($pengumuman->target_role)) {
            return view('pengumuman.show', compact('pengumuman'));
        }
        abort(403, 'ANDA TIDAK MEMILIKI WEWENANG UNTUK MELIHAT INI.');
    }

    public function edit(Pengumuman $pengumuman): View { return view('pengumuman.edit', compact('pengumuman')); }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|in:berita,pengumuman',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'konten' => 'required|string',
            'target_role' => 'required|string|in:semua,admin,dosen,mahasiswa,tendik',
        ]);
        $data = $request->only('judul', 'kategori', 'konten', 'target_role');
        if ($request->hasFile('foto')) {
            if ($pengumuman->foto) { Storage::disk('public')->delete($pengumuman->foto); }
            $data['foto'] = $request->file('foto')->store('pengumuman', 'public');
        }
        $pengumuman->update($data);
        return redirect()->route('admin.pengumuman.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        if ($pengumuman->foto) { Storage::disk('public')->delete($pengumuman->foto); }
        $pengumuman->delete();
        return redirect()->route('admin.pengumuman.index')->with('success', 'Data berhasil dihapus.');
    }
}