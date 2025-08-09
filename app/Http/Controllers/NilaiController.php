<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    /**
     * Menampilkan daftar mata kuliah untuk dipilih (HANYA UNTUK ADMIN).
     */
    public function index()
    {
        // Hanya admin yang boleh melihat halaman ini
        if (Auth::user()->role != 'admin') {
            abort(403, 'AKSES DITOLAK');
        }

        // Gunakan with('dosen') untuk memuat relasi agar efisien
        $mata_kuliahs = MataKuliah::with('dosen')->get();
        return view('nilai.index', compact('mata_kuliahs'));
    }

    /**
     * Menampilkan form untuk input nilai (ADMIN & DOSEN PENGAMPU).
     */
    public function show(MataKuliah $mataKuliah)
    {
        $user = Auth::user();

        // Cek apakah user adalah admin ATAU dosen yang mengajar mata kuliah ini
        if ($user->role == 'admin' || ($user->role == 'dosen' && $user->dosen?->id == $mataKuliah->dosen_id)) {
            $mataKuliah->load('mahasiswas'); // Eager load relasi mahasiswas
            return view('nilai.show', compact('mataKuliah'));
        }

        // Jika tidak, tolak akses
        abort(403, 'AKSES DITOLAK');
    }

    /**
     * Menyimpan nilai yang diinput (ADMIN & DOSEN PENGAMPU).
     */
    public function store(Request $request)
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'nilai.*' => 'nullable|string|max:2',
        ]);
    
        $mataKuliah = MataKuliah::find($request->mata_kuliah_id);
        $user = Auth::user();

        // Cek sekali lagi apakah user adalah admin ATAU dosen yang mengajar
        if ($user->role == 'admin' || ($user->role == 'dosen' && $user->dosen?->id == $mataKuliah->dosen_id)) {
            foreach ($request->nilai as $mahasiswa_id => $nilai) {
                // Gunakan updateExistingPivot untuk update data di tabel pivot
                $mataKuliah->mahasiswas()->updateExistingPivot($mahasiswa_id, ['nilai' => $nilai]);
            }

            // Arahkan kembali sesuai peran
            $redirectRoute = $user->role == 'admin' ? 'nilai.index' : 'dosen.dashboard';
            return redirect()->route($redirectRoute)->with('success', 'Nilai berhasil disimpan!');
        }
        
        // Jika tidak, tolak akses
        abort(403, 'AKSES DITOLAK');
    }
}