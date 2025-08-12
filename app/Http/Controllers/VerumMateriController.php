<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumMateri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VerumMateriController extends Controller
{
    /**
     * Menyimpan materi baru ke database.
     */
    public function store(Request $request, VerumKelas $verum_kela)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file_materi' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip|max:10240', // Maks 10MB
            'link_url' => 'nullable|url',
        ]);

        // Pastikan salah satu dari file atau link diisi
        if (!$request->hasFile('file_materi') && !$request->filled('link_url')) {
            return back()->withErrors(['file_materi' => 'Anda harus mengunggah file atau memasukkan link.'])->withInput();
        }

        $filePath = null;
        if ($request->hasFile('file_materi')) {
            // Simpan file ke storage/app/public/materi
            $filePath = $request->file('file_materi')->store('public/materi');
        }

        VerumMateri::create([
            'kelas_id' => $verum_kela->id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'file_path' => $filePath,
            'link_url' => $request->link_url,
        ]);

        return back()->with('success', 'Materi berhasil ditambahkan.');
    }

    /**
     * Menghapus materi dari database dan storage.
     */
    public function destroy(VerumMateri $verum_materi)
    {
        // TODO: Tambahkan otorisasi untuk memastikan hanya dosen kelas ini yang bisa menghapus

        // Hapus file dari storage jika ada
        if ($verum_materi->file_path) {
            Storage::delete($verum_materi->file_path);
        }

        $verum_materi->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}
