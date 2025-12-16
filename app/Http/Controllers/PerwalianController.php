<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerwalianController extends Controller
{
    /**
     * Menampilkan daftar mahasiswa perwalian.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->dosen) {
            abort(403, 'Data dosen tidak ditemukan. Pastikan profil dosen Anda sudah lengkap.');
        }
        $dosen = $user->dosen;

        // --- DAFTAR MAHASISWA PERWALIAN SAAT INI ---
        $mahasiswa_wali = Mahasiswa::where('dosen_wali_id', $dosen->id)
                            ->with('programStudi')
                            ->orderBy('nama_lengkap')
                            ->get();

        // --- QUERY PENCARIAN MAHASISWA BARU ---
        $query = Mahasiswa::whereNull('dosen_wali_id')
                    ->with('programStudi')
                    ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        if ($request->filled('program_studi_id')) {
            $query->where('program_studi_id', $request->input('program_studi_id'));
        }

        if ($request->filled('angkatan')) {
            $query->where('tahun_masuk', $request->input('angkatan'));
        }

        $mahasiswa_tersedia = $query->paginate(10)->withQueryString();
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        $angkatans = Mahasiswa::whereNull('dosen_wali_id')
                        ->select('tahun_masuk')->distinct()->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk');

        return view('perwalian.index', compact('mahasiswa_wali', 'mahasiswa_tersedia', 'program_studis', 'angkatans', 'dosen'));
    }

    /**
     * [BARU] Menampilkan Detail KRS Mahasiswa & Menu Validasi.
     */
    public function show($id): View
    {
        $user = Auth::user();
        if (!$user->dosen) abort(403);
        $dosen = $user->dosen;

        // 1. Pastikan mahasiswa ini benar-benar bimbingan dosen yg login
        $mahasiswa = Mahasiswa::where('id', $id)
                        ->where('dosen_wali_id', $dosen->id)
                        ->firstOrFail();

        // 2. Ambil KRS (Mata Kuliah yang diambil)
        $krs = $mahasiswa->mataKuliahs()->with('jadwals')->get();
        $totalSks = $krs->sum('sks');

        return view('perwalian.show', compact('mahasiswa', 'krs', 'totalSks'));
    }

    /**
     * [BARU] Mengubah Status KRS (Disetujui / Ditolak / Menunggu).
     */
    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $dosen = $user->dosen;

        $mahasiswa = Mahasiswa::where('id', $id)->where('dosen_wali_id', $dosen->id)->firstOrFail();

        $request->validate([
            'status_krs' => 'required|in:Disetujui,Ditolak,Menunggu Persetujuan'
        ]);

        $mahasiswa->status_krs = $request->status_krs;
        $mahasiswa->save();

        $pesan = $request->status_krs == 'Disetujui' ? 'KRS Mahasiswa berhasil DISETUJUI.' : 'Status KRS berhasil diperbarui.';

        return redirect()->back()->with('success', $pesan);
    }

    /**
     * Menambahkan mahasiswa ke daftar perwalian (Klaim).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mahasiswa_ids' => 'required|array',
            'mahasiswa_ids.*' => 'exists:mahasiswas,id',
        ]);

        $user = Auth::user();
        if (!$user->dosen) abort(403);
        $dosen = $user->dosen;

        if ($request->has('mahasiswa_ids')) {
            Mahasiswa::whereIn('id', $request->mahasiswa_ids)
                     ->whereNull('dosen_wali_id')
                     ->update(['dosen_wali_id' => $dosen->id]);
        }

        return redirect()->route('perwalian.index')->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    /**
     * Melepas mahasiswa dari perwalian.
     */
    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        $user = Auth::user();
        $dosen = $user->dosen;

        if ($mahasiswa->dosen_wali_id == $dosen->id) {
            $mahasiswa->update(['dosen_wali_id' => null]);
            return redirect()->route('perwalian.index')->with('success', 'Mahasiswa dilepas dari perwalian.');
        }

        return redirect()->route('perwalian.index')->with('error', 'Akses ditolak.');
    }

    /**
     * Force Delete KRS (Revisi Dosen).
     */
    public function destroyKrs($mahasiswa_id, $mk_id): RedirectResponse
    {
        $user = Auth::user();
        $dosen = $user->dosen;

        $mahasiswa = Mahasiswa::where('id', $mahasiswa_id)
                        ->where('dosen_wali_id', $dosen->id)
                        ->firstOrFail();

        $mahasiswa->mataKuliahs()->detach($mk_id);

        return redirect()->back()->with('success', 'Mata kuliah berhasil dihapus (Revisi).');
    }
}