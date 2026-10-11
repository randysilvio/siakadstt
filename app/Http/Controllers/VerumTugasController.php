<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumTugas;
use App\Models\VerumPengumpulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View; // Pastikan ini di-import

class VerumTugasController extends Controller
{
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        if (!Auth::user()->dosen || Auth::user()->dosen->id !== $verum_kela->dosen_id) {
            abort(403, 'Aksi ini hanya untuk dosen pengampu kelas.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'instruksi' => 'required|string',
            'tenggat_waktu' => 'required|date',
        ]);

        $verum_kela->tugas()->create($request->all());

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function destroy(VerumTugas $verum_tuga): RedirectResponse
    {
        if (!Auth::user()->dosen || Auth::user()->dosen->id !== $verum_tuga->kelas->dosen_id) {
            abort(403, 'Anda tidak berhak menghapus tugas ini.');
        }

        foreach($verum_tuga->pengumpulan as $kumpul) {
            if ($kumpul->file_path) Storage::disk('public')->delete($kumpul->file_path);
        }
        
        $verum_tuga->delete();
        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    // [BARU] Fungsi untuk Menampilkan Halaman Rekap Tugas Dosen
    public function rekap(VerumTugas $verum_tuga): View
    {
        if (!Auth::user()->dosen || Auth::user()->dosen->id !== $verum_tuga->kelas->dosen_id) {
            abort(403, 'Akses ditolak. Anda bukan pengampu kelas ini.');
        }

        // Ambil semua mahasiswa yang mengambil mata kuliah ini (dari KRS)
        $mahasiswas = $verum_tuga->kelas->mataKuliah->mahasiswas()->orderBy('nama_lengkap')->get();

        // Ambil data pengumpulan khusus untuk tugas ini dan jadikan koleksi berdasarkan ID Mahasiswa
        $pengumpulan = VerumPengumpulan::where('tugas_id', $verum_tuga->id)
                                       ->get()
                                       ->keyBy('mahasiswa_id');

        return view('verum.tugas_rekap', compact('verum_tuga', 'mahasiswas', 'pengumpulan'));
    }

    public function storePengumpulan(Request $request, VerumTugas $verum_tuga): RedirectResponse
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) abort(403, 'Aksi ini hanya untuk mahasiswa.');

        $isEnrolled = $mahasiswa->mataKuliahs->contains($verum_tuga->kelas->mata_kuliah_id);
        if (!$isEnrolled) {
            return back()->with('error', 'Anda tidak terdaftar di mata kuliah ini.');
        }

        if (now()->isAfter($verum_tuga->tenggat_waktu)) {
            return back()->with('error', 'Gagal. Batas waktu pengumpulan tugas sudah ditutup.');
        }

        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,doc,docx,zip|max:10240', // Maks 10MB
        ], [
            'file_jawaban.required' => 'Anda harus memilih file untuk diunggah.',
            'file_jawaban.mimes' => 'Format file harus PDF, DOC, DOCX, atau ZIP.',
            'file_jawaban.max' => 'Ukuran file maksimal adalah 10MB.'
        ]);

        $filePath = $request->file('file_jawaban')->store('jawaban_tugas', 'public');

        VerumPengumpulan::updateOrCreate(
            [
                'tugas_id' => $verum_tuga->id,
                'mahasiswa_id' => $mahasiswa->id,
            ],
            [
                'file_path' => $filePath,
                'waktu_pengumpulan' => now(),
            ]
        );

        return back()->with('success', 'Jawaban tugas berhasil dikumpulkan.');
    }
}