<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Auth;

class PerwalianController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }

        $mahasiswa_wali = Mahasiswa::where('dosen_wali_id', $dosen->id)->with('programStudi')->get();
        $mahasiswa_tersedia = Mahasiswa::whereNull('dosen_wali_id')->with('programStudi')->get();

        return view('perwalian.index', compact('mahasiswa_wali', 'mahasiswa_tersedia'));
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

    /**
     * Fungsi baru untuk menghapus perwalian.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        $dosen = Auth::user()->dosen;

        // Pengecekan keamanan: pastikan dosen hanya bisa menghapus perwaliannya sendiri
        if ($mahasiswa->dosen_wali_id == $dosen->id) {
            $mahasiswa->update(['dosen_wali_id' => null]);
            return redirect()->route('perwalian.index')->with('success', 'Mahasiswa berhasil dihapus dari perwalian.');
        }

        return redirect()->route('perwalian.index')->with('error', 'Aksi tidak diizinkan.');
    }
}