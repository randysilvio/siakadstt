<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranskripController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }

        // Ambil semua mata kuliah yang sudah dinilai, diurutkan per semester
        $krs = $mahasiswa->mataKuliahs()
                         ->wherePivotNotNull('nilai')
                         ->orderBy('semester')
                         ->get();

        // Kelompokkan mata kuliah berdasarkan semester
        $krs_per_semester = $krs->groupBy('semester');

        // Logika perhitungan IPK (sama seperti di dashboard)
        $total_sks = 0;
        $total_bobot_sks = 0;
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

        foreach ($krs as $mk) {
            $sks = $mk->sks;
            $nilai = $mk->pivot->nilai;
            
            if (isset($bobot_nilai[$nilai])) {
                $total_sks += $sks;
                $total_bobot_sks += ($bobot_nilai[$nilai] * $sks);
            }
        }

        $ipk = ($total_sks > 0) ? round($total_bobot_sks / $total_sks, 2) : 0;

        return view('transkrip.index', compact('mahasiswa', 'krs_per_semester', 'total_sks', 'ipk'));
    }
}