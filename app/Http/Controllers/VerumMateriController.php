<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumMateri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerumMateriController extends Controller
{
    public function __construct()
    {
        // Pastikan hanya role dosen yang bisa akses controller ini
        // Sesuai aturan: Admin DIBLOKIR dari operasional
        $this->middleware(['auth', 'role:dosen']);
    }

    /**
     * Menyimpan materi baru ke database dengan sanitasi input.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        // 1. Authorization: Pastikan dosen ini benar-benar pengajar kelas tersebut
        $this->authorize('update', $verum_kela);

        // 2. Validation: Strict File Type & URL
        $request->validate([
            'judul'       => 'required|string|max:150', // Batasi panjang judul
            'deskripsi'   => 'nullable|string|max:1000',
            // Gunakan mimetypes untuk validasi isi file, bukan hanya ekstensi
            'file_materi' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip|max:10240', 
            'link_url'    => 'nullable|url|active_url', // active_url memastikan DNS valid (opsional)
        ], [
            'file_materi.max' => 'Ukuran file tidak boleh lebih dari 10MB.',
            'file_materi.mimes' => 'Format file harus dokumen (PDF, Office) atau ZIP.',
        ]);

        // Logic cek input kosong
        if (!$request->hasFile('file_materi') && !$request->filled('link_url')) {
            return back()->withErrors(['file_materi' => 'Anda harus mengunggah file atau memasukkan link referensi.'])->withInput();
        }

        $filePath = null;
        if ($request->hasFile('file_materi')) {
            // Store file dengan hash name di folder 'materi' disk 'public'
            $filePath = $request->file('file_materi')->store('materi', 'public');
        }

        // 3. Simpan ke Database dengan Sanitasi (Mencegah XSS)
        $verum_kela->materi()->create([
            'judul'     => strip_tags($request->judul), // Hapus tag HTML berbahaya
            'deskripsi' => strip_tags($request->deskripsi),
            'file_path' => $filePath,
            'link_url'  => $request->link_url,
        ]);

        return back()->with('success', 'Materi berhasil ditambahkan.');
    }

    /**
     * Menghapus materi secara aman.
     */
    public function destroy(VerumMateri $verum_materi): RedirectResponse
    {
        // 1. Authorization: Cek kepemilikan
        $this->authorize('delete', $verum_materi);

        // 2. Hapus fisik file jika ada
        if ($verum_materi->file_path && Storage::disk('public')->exists($verum_materi->file_path)) {
            Storage::disk('public')->delete($verum_materi->file_path);
        }

        // 3. Hapus record DB
        $verum_materi->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}