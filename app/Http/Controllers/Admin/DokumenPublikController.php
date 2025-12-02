<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DokumenPublik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokumenPublikController extends Controller
{
    /**
     * Menampilkan daftar dokumen dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        $query = DokumenPublik::latest();

        // Filter Pencarian Judul
        if ($request->filled('search')) {
            $query->where('judul_dokumen', 'like', '%' . $request->input('search') . '%');
        }

        $dokumens = $query->paginate(10)->withQueryString();
        
        return view('admin.dokumen-publik.index', compact('dokumens'));
    }

    /**
     * Menampilkan formulir untuk membuat dokumen baru.
     */
    public function create()
    {
        return view('admin.dokumen-publik.create');
    }

    /**
     * Menyimpan dokumen baru ke database dan storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul_dokumen' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file_dokumen' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // Max 10MB biar aman
        ]);

        // Simpan file
        $filePath = $request->file('file_dokumen')->store('dokumen-publik', 'public');

        DokumenPublik::create([
            'judul_dokumen' => $request->judul_dokumen,
            'deskripsi' => $request->deskripsi,
            'file_path' => $filePath,
        ]);

        // [PERBAIKAN] Menggunakan nama rute admin yang benar
        return redirect()->route('admin.dokumen-publik.index')->with('success', 'Dokumen publik berhasil diunggah.');
    }

    /**
     * Menghapus dokumen dari database dan storage.
     */
    public function destroy(DokumenPublik $dokumen_publik)
    {
        // Hapus file dari storage
        if (Storage::disk('public')->exists($dokumen_publik->file_path)) {
            Storage::disk('public')->delete($dokumen_publik->file_path);
        }

        $dokumen_publik->delete();

        // [PERBAIKAN] Menggunakan nama rute admin yang benar
        return redirect()->route('admin.dokumen-publik.index')->with('success', 'Dokumen publik berhasil dihapus.');
    }
}