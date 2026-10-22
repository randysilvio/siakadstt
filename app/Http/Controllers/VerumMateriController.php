<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumMateri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerumMateriController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:dosen']);
    }

    /**
     * Helper untuk mengecek apakah dosen adalah pengampu utama atau anggota Team Teaching.
     */
    private function isDosenPengampu($user, VerumKelas $kelas): bool
    {
        if (!$user || !$user->dosen) {
            return false;
        }
        if ((int) $user->dosen->id === (int) $kelas->dosen_id) {
            return true;
        }
        return DB::table('dosen_mata_kuliah')
            ->where('mata_kuliah_id', $kelas->mata_kuliah_id)
            ->where('dosen_id', $user->dosen->id)
            ->exists();
    }

    /**
     * Menyimpan materi baru ke database dengan sanitasi input.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        if (!$this->isDosenPengampu(Auth::user(), $verum_kela)) {
            abort(403, 'Aksi ini hanya untuk dosen pengampu kelas.');
        }

        $request->validate([
            'judul'       => 'required|string|max:150',
            'deskripsi'   => 'nullable|string|max:1000',
            'file_materi' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip|max:10240', 
            'link_url'    => 'nullable|url|active_url', 
        ], [
            'file_materi.max' => 'Ukuran file tidak boleh lebih dari 10MB.',
            'file_materi.mimes' => 'Format file harus dokumen (PDF, Office) atau ZIP.',
        ]);

        if (!$request->hasFile('file_materi') && (!$request->filled('link_url') || $request->link_url == null)) {
            return back()->withErrors(['file_materi' => 'Anda harus mengunggah file atau memasukkan link referensi.'])->withInput();
        }

        $filePath = null;
        if ($request->hasFile('file_materi')) {
            $filePath = $request->file('file_materi')->store('materi', 'public');
        }

        $verum_kela->materi()->create([
            'judul'     => strip_tags($request->judul),
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
        if (!$verum_materi->kelas || !$this->isDosenPengampu(Auth::user(), $verum_materi->kelas)) {
            abort(403, 'Anda tidak berhak menghapus materi ini.');
        }

        if ($verum_materi->file_path && Storage::disk('public')->exists($verum_materi->file_path)) {
            Storage::disk('public')->delete($verum_materi->file_path);
        }

        $verum_materi->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}