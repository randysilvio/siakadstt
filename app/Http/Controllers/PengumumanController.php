<?php

namespace App\Http\Controllers;

use App\Support\HtmlSanitizer;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;

class PengumumanController extends Controller
{
    /**
     * Sanitasi konten HTML dari Trix Editor terhadap Stored XSS (parser DOM + whitelist, lihat HtmlSanitizer).
     */
    private function sanitizeHtml(?string $html): string
    {
        return HtmlSanitizer::clean($html);
    }

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

        $data = [
            'judul' => strip_tags($request->judul),
            'kategori' => $request->kategori,
            'konten' => $this->sanitizeHtml($request->konten),
            'target_role' => $request->target_role,
            'user_id' => Auth::id(),
            'slug' => Str::slug($request->judul) . '-' . Str::lower(Str::random(5)),
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('pengumuman', 'public');
        }

        $pengumuman = Pengumuman::create($data);

        // Distribusi Notifikasi
        if ($request->target_role == 'semua') {
            $users = User::all();
            $targetUrl = route('pengumuman.public.show', $pengumuman->slug);
        } else {
            $users = User::whereHas('roles', function ($q) use ($request) {
                $q->whereIn('name', Pengumuman::roleSasaran($request->target_role));
            })->get();
            $targetUrl = route('pengumuman.internal.show', $pengumuman->slug);
        }
        
        if ($users->isNotEmpty()) {
            Notification::send($users, new GeneralNotification(
                ucfirst($request->kategori) . ' Baru',
                $data['judul'],
                $targetUrl,
                'bi-megaphone-fill text-info'
            ));
        }

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil ditambahkan dan notifikasi dikirim.');
    }

    /**
     * Halaman baca pengumuman internal (bertarget peran tertentu) bagi pengguna yang login.
     */
    public function bacaInternal(Pengumuman $pengumuman): View
    {
        if (!$pengumuman->bisaDibacaOleh(Auth::user())) {
            abort(403, 'Pengumuman ini tidak ditujukan untuk peran akun Anda.');
        }

        return view('pengumuman.internal_show', compact('pengumuman'));
    }

    public function show(Pengumuman $pengumuman): View
    {
        return view('pengumuman.show', compact('pengumuman'));
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

        $data = [
            'judul' => strip_tags($request->judul),
            'kategori' => $request->kategori,
            'konten' => $this->sanitizeHtml($request->konten),
            'target_role' => $request->target_role,
        ];
        
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