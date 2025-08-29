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
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }
        $dosen = $user->dosen;

        // Ambil daftar mahasiswa yang sudah menjadi wali
        $mahasiswa_wali = Mahasiswa::where('dosen_wali_id', $dosen->id)->with('programStudi')->get();

        // Query untuk mahasiswa yang belum punya wali
        $query = Mahasiswa::whereNull('dosen_wali_id')->with('programStudi');

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

        $mahasiswa_tersedia = $query->paginate(10)->withQueryString();
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();

        return view('perwalian.index', compact('mahasiswa_wali', 'mahasiswa_tersedia', 'program_studis'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mahasiswa_ids' => 'nullable|array',
            'mahasiswa_ids.*' => 'exists:mahasiswas,id',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->dosen) {
            abort(403, 'Aksi tidak diizinkan.');
        }
        $dosen = $user->dosen;

        if ($request->has('mahasiswa_ids')) {
            Mahasiswa::whereIn('id', $request->mahasiswa_ids)
                     ->update(['dosen_wali_id' => $dosen->id]);
        }

        return redirect()->route('perwalian.index')->with('success', 'Data mahasiswa perwalian berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->dosen) {
            abort(403, 'Aksi tidak diizinkan.');
        }
        $dosen = $user->dosen;

        // Pastikan dosen hanya bisa menghapus mahasiswa walinya sendiri
        if ($mahasiswa->dosen_wali_id == $dosen->id) {
            $mahasiswa->update(['dosen_wali_id' => null]);
            return redirect()->route('perwalian.index')->with('success', 'Mahasiswa berhasil dihapus dari perwalian.');
        }

        return redirect()->route('perwalian.index')->with('error', 'Aksi tidak diizinkan.');
    }
}
