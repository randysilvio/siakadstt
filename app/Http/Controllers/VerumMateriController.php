<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumMateri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;

class VerumMateriController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:dosen');
    }

    /**
     * Menyimpan materi baru ke database.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        $this->authorize('update', $verum_kela);

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file_materi' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip|max:10240',
            'link_url' => 'nullable|url',
        ]);

        if (!$request->hasFile('file_materi') && !$request->filled('link_url')) {
            return back()->withErrors(['file_materi' => 'Anda harus mengunggah file atau memasukkan link.'])->withInput();
        }

        $filePath = null;
        if ($request->hasFile('file_materi')) {
            $filePath = $request->file('file_materi')->store('materi', 'public');
        }

        $verum_kela->materi()->create([
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
    public function destroy(VerumMateri $verum_materi): RedirectResponse
    {
        $this->authorize('delete', $verum_materi);

        if ($verum_materi->file_path) {
            Storage::disk('public')->delete($verum_materi->file_path);
        }

        $verum_materi->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}