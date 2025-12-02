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
     * Menampilkan halaman manajemen perwalian dengan Smart Filter.
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

        // --- QUERY PENCARIAN MAHASISWA BARU (YANG BELUM PUNYA WALI) ---
        $query = Mahasiswa::whereNull('dosen_wali_id')
                    ->with('programStudi')
                    ->latest();

        // 1. Filter Pencarian Teks (Nama/NIM)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        // 2. Filter Program Studi
        if ($request->filled('program_studi_id')) {
            $query->where('program_studi_id', $request->input('program_studi_id'));
        }

        // 3. [BARU] Filter Angkatan
        if ($request->filled('angkatan')) {
            $query->where('tahun_masuk', $request->input('angkatan'));
        }

        $mahasiswa_tersedia = $query->paginate(10)->withQueryString();
        
        // Data Pendukung untuk Filter
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        
        // Ambil daftar tahun masuk unik
        $angkatans = Mahasiswa::whereNull('dosen_wali_id')
                        ->select('tahun_masuk')
                        ->distinct()
                        ->orderBy('tahun_masuk', 'desc')
                        ->pluck('tahun_masuk');

        return view('perwalian.index', compact('mahasiswa_wali', 'mahasiswa_tersedia', 'program_studis', 'angkatans', 'dosen'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mahasiswa_ids' => 'required|array',
            'mahasiswa_ids.*' => 'exists:mahasiswas,id',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->dosen) { abort(403, 'Aksi tidak diizinkan.'); }
        $dosen = $user->dosen;

        if ($request->has('mahasiswa_ids')) {
            // Update mahasiswa yang dipilih agar dosen_wali_id-nya menjadi ID dosen ini
            Mahasiswa::whereIn('id', $request->mahasiswa_ids)
                     ->whereNull('dosen_wali_id') // Keamanan ekstra: pastikan belum punya wali
                     ->update(['dosen_wali_id' => $dosen->id]);
        }

        return redirect()->route('perwalian.index')->with('success', 'Mahasiswa berhasil ditambahkan ke daftar perwalian Anda.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->dosen) { abort(403, 'Aksi tidak diizinkan.'); }
        $dosen = $user->dosen;

        if ($mahasiswa->dosen_wali_id == $dosen->id) {
            $mahasiswa->update(['dosen_wali_id' => null]);
            return redirect()->route('perwalian.index')->with('success', 'Mahasiswa berhasil dihapus dari daftar perwalian.');
        }

        return redirect()->route('perwalian.index')->with('error', 'Anda tidak memiliki hak akses ke mahasiswa ini.');
    }
}