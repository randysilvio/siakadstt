<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi; // <-- Tambahkan ini
use Illuminate\Support\Facades\Auth;

class PerwalianController extends Controller
{
    public function index(Request $request) // <-- Tambahkan Request
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        // Ambil daftar mahasiswa yang sudah menjadi wali (tidak perlu paginasi)
        $mahasiswa_wali = Mahasiswa::where('dosen_wali_id', $dosen->id)->with('programStudi')->get();

        // =================================================================
        // ===== PERBAIKAN: Menambahkan Logika Filter, Pencarian, & Paginasi =====
        // =================================================================
        $query = Mahasiswa::whereNull('dosen_wali_id')->with('programStudi');

        // 1. Terapkan filter pencarian jika ada
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        // 2. Terapkan filter program studi jika ada
        if ($request->filled('program_studi_id')) {
            $query->where('program_studi_id', $request->input('program_studi_id'));
        }

        // 3. Ambil data dengan paginasi (10 per halaman)
        $mahasiswa_tersedia = $query->paginate(10)->withQueryString();

        // Ambil semua program studi untuk ditampilkan di dropdown filter
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        // =================================================================

        return view('perwalian.index', compact('mahasiswa_wali', 'mahasiswa_tersedia', 'program_studis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_ids' => 'nullable|array',
            'mahasiswa_ids.*' => 'exists:mahasiswas,id',
        ]);

        $dosen = Auth::user()->dosen;

        if ($request->has('mahasiswa_ids')) {
            Mahasiswa::whereIn('id', $request->mahasiswa_ids)
                     ->update(['dosen_wali_id' => $dosen->id]);
        }

        return redirect()->route('perwalian.index')->with('success', 'Data mahasiswa perwalian berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        $dosen = Auth::user()->dosen;

        if ($mahasiswa->dosen_wali_id == $dosen->id) {
            $mahasiswa->update(['dosen_wali_id' => null]);
            return redirect()->route('perwalian.index')->with('success', 'Mahasiswa berhasil dihapus dari perwalian.');
        }

        return redirect()->route('perwalian.index')->with('error', 'Aksi tidak diizinkan.');
    }
}
