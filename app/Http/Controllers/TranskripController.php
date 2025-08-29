<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TranskripController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa;

        // Ambil semua mata kuliah yang sudah dinilai
        $krs = $mahasiswa->mataKuliahs()
                         ->wherePivotNotNull('nilai')
                         ->get();

        // Kelompokkan mata kuliah berdasarkan semester dari tabel mata_kuliahs
        $krs_per_semester = $krs->groupBy('semester');

        // Gunakan method dari model untuk menghitung IPK
        $ipk = $mahasiswa->hitungIpk();
        $total_sks = $mahasiswa->totalSksLulus();


        return view('transkrip.index', compact('mahasiswa', 'krs_per_semester', 'total_sks', 'ipk'));
    }
}
