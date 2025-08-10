<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DosenDashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard untuk dosen.
     */
    public function index()
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }
        
        // Ambil mata kuliah yang diajar oleh dosen yang login,
        // dan hitung jumlah mahasiswa yang mengambilnya.
        $mata_kuliahs = $dosen->mataKuliahs()->withCount('mahasiswas')->get();

        return view('dosen.dashboard', compact('dosen', 'mata_kuliahs'));
    }
}
