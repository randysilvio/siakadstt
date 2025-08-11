<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgramStudi;

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
        
        $mata_kuliahs = $dosen->mataKuliahs()->withCount('mahasiswas')->get();
        $jumlahMahasiswaWali = $dosen->mahasiswaWali()->count();

        // Cek apakah dosen ini adalah Kaprodi di salah satu prodi
        $prodiYangDikepalai = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->first();

        // Kirim semua variabel yang dibutuhkan ke view
        return view('dosen.dashboard', compact('dosen', 'mata_kuliahs', 'jumlahMahasiswaWali', 'prodiYangDikepalai'));
    }
}
