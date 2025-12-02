<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slideshow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SlideshowController extends Controller
{
    /**
     * Display a listing of the resource with Smart Filter.
     */
    public function index(Request $request)
    {
        $query = Slideshow::orderBy('urutan', 'asc');

        // 1. Filter Pencarian Judul
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->input('search') . '%');
        }

        // 2. Filter Status Aktif
        if ($request->filled('status')) {
            if ($request->input('status') == 'aktif') {
                $query->where('is_aktif', true);
            } elseif ($request->input('status') == 'tidak_aktif') {
                $query->where('is_aktif', false);
            }
        }

        $slides = $query->paginate(10)->withQueryString();
        
        return view('admin.slideshow.index', compact('slides'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.slideshow.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'nullable|string|max:255',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'urutan' => 'required|integer|min:0',
            'is_aktif' => 'sometimes|boolean',
        ]);

        $gambarPath = $request->file('gambar')->store('slideshows', 'public');

        Slideshow::create([
            'judul' => $request->judul,
            'gambar' => $gambarPath,
            'urutan' => $request->urutan ?? 0,
            'is_aktif' => $request->has('is_aktif'),
        ]);

        return redirect()->route('admin.slideshows.index')->with('success', 'Slide berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Slideshow $slideshow)
    {
        return view('admin.slideshow.edit', compact('slideshow'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Slideshow $slideshow)
    {
        $request->validate([
            'judul' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'urutan' => 'required|integer|min:0',
            'is_aktif' => 'sometimes|boolean',
        ]);

        $slideshow->judul = $request->judul;
        $slideshow->urutan = $request->urutan ?? 0;
        $slideshow->is_aktif = $request->has('is_aktif');

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($slideshow->gambar && Storage::disk('public')->exists($slideshow->gambar)) {
                Storage::disk('public')->delete($slideshow->gambar);
            }
            // Simpan gambar baru
            $slideshow->gambar = $request->file('gambar')->store('slideshows', 'public');
        }

        $slideshow->save();

        return redirect()->route('admin.slideshows.index')->with('success', 'Slide berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slideshow $slideshow)
    {
        // Hapus gambar dari storage
        if ($slideshow->gambar && Storage::disk('public')->exists($slideshow->gambar)) {
            Storage::disk('public')->delete($slideshow->gambar);
        }

        $slideshow->delete();

        return redirect()->route('admin.slideshows.index')->with('success', 'Slide berhasil dihapus.');
    }
}