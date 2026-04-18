<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\User; // [TAMBAHAN]
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Notification; // [TAMBAHAN]
use App\Notifications\GeneralNotification; // [TAMBAHAN]

class PengumumanController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pengumuman::latest();

        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->input('search') . '%');
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }
        if ($request->filled('target_role')) {
            $query->where('target_role', $request->input('target_role'));
        }

        $pengumumans = $query->paginate(10)->withQueryString();
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
            'kategori' => 'required|in:berita,pengumuman',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'konten' => 'required|string',
            'target_role' => 'required|string|in:semua,admin,dosen,mahasiswa,tendik',
        ]);

        $data = $request->only('judul', 'kategori', 'konten', 'target_role');
        $data['user_id'] = Auth::id();
        $data['slug'] = Str::slug($request->judul) . '-' . Str::lower(Str::random(5));

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('pengumuman', 'public');
        }

        $pengumuman = Pengumuman::create($data);

        // [TAMBAHAN] Distribusi Notifikasi
        if ($request->target_role == 'semua') {
            $users = User::all();
        } else {
            $users = User::whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->target_role);
            })->get();
        }
        
        Notification::send($users, new GeneralNotification(
            ucfirst($request->kategori) . ' Baru',
            $request->judul,
            route('pengumuman.public.show', $pengumuman->slug),
            'bi-megaphone-fill text-info'
        ));

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil ditambahkan dan notifikasi dikirim.');
    }

    public function edit(Pengumuman $pengumuman): View
    {
        return view('pengumuman.edit', compact('pengumuman'));
    }

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
        
        if ($request->judul !== $pengumuman->judul) {
             $data['slug'] = Str::slug($request->judul) . '-' . Str::lower(Str::random(5));
        }

        if ($request->hasFile('foto')) {
            if ($pengumuman->foto && Storage::disk('public')->exists($pengumuman->foto)) { 
                Storage::disk('public')->delete($pengumuman->foto); 
            }
            $data['foto'] = $request->file('foto')->store('pengumuman', 'public');
        }

        $pengumuman->update($data);
        return redirect()->route('admin.pengumuman.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        if ($pengumuman->foto && Storage::disk('public')->exists($pengumuman->foto)) { 
            Storage::disk('public')->delete($pengumuman->foto); 
        }
        $pengumuman->delete();
        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}