<?php

namespace App\Http\Controllers;

use App\Models\Koleksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KoleksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $koleksis = Koleksi::latest()->paginate(10);
        return view('perpustakaan.koleksi.index', compact('koleksis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('perpustakaan.koleksi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|digits:4',
            'isbn' => 'nullable|string|unique:perpustakaan_koleksi,isbn',
            'jumlah_stok' => 'required|integer|min:1',
            'lokasi_rak' => 'required|string|max:255',
            'sinopsis' => 'nullable|string',
            'gambar_sampul' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Maks 2MB
        ]);

        $data = $request->all();
        // Saat buku baru ditambahkan, jumlah yang tersedia sama dengan jumlah stok
        $data['jumlah_tersedia'] = $request->jumlah_stok;

        if ($request->hasFile('gambar_sampul')) {
            $path = $request->file('gambar_sampul')->store('public/sampul_buku');
            $data['gambar_sampul'] = $path;
        }

        Koleksi::create($data);

        return redirect()->route('perpustakaan.koleksi.index')->with('success', 'Buku berhasil ditambahkan.');
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Koleksi $koleksi)
    {
        return view('perpustakaan.koleksi.show', compact('koleksi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Koleksi $koleksi)
    {
        return view('perpustakaan.koleksi.edit', compact('koleksi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Koleksi $koleksi)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|digits:4',
            'isbn' => 'nullable|string|unique:perpustakaan_koleksi,isbn,' . $koleksi->id,
            'jumlah_stok' => 'required|integer|min:0',
            'lokasi_rak' => 'required|string|max:255',
            'sinopsis' => 'nullable|string',
            'gambar_sampul' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        // Logika penyesuaian jumlah tersedia saat stok diubah
        $selisihStok = $request->jumlah_stok - $koleksi->jumlah_stok;
        $data['jumlah_tersedia'] = $koleksi->jumlah_tersedia + $selisihStok;
        if ($data['jumlah_tersedia'] < 0) {
            $data['jumlah_tersedia'] = 0; // Pastikan tidak menjadi negatif
        }

        if ($request->hasFile('gambar_sampul')) {
            // Hapus gambar lama jika ada
            if ($koleksi->gambar_sampul) {
                Storage::delete($koleksi->gambar_sampul);
            }
            $path = $request->file('gambar_sampul')->store('public/sampul_buku');
            $data['gambar_sampul'] = $path;
        }

        $koleksi->update($data);

        return redirect()->route('perpustakaan.koleksi.index')->with('success', 'Data buku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Koleksi $koleksi)
    {
        // TODO: Tambahkan pengecekan apakah buku sedang dipinjam sebelum dihapus
        
        // Hapus gambar sampul dari storage jika ada
        if ($koleksi->gambar_sampul) {
            Storage::delete($koleksi->gambar_sampul);
        }

        $koleksi->delete();
        return redirect()->route('perpustakaan.koleksi.index')->with('success', 'Buku berhasil dihapus.');
    }
}