<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pengaturan;
use App\Models\Mahasiswa;

class CetakController extends Controller
{
    /**
     * Generate PDF untuk Kartu Hasil Studi (KHS).
     */
    public function cetakKhs()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }

        $krs = $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->get();

        // Logika perhitungan IPS
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

        $ips = ($total_sks > 0) ? round($total_bobot_sks / $total_sks, 2) : 0;

        $pdf = Pdf::loadView('khs.pdf', compact('mahasiswa', 'krs', 'total_sks', 'ips'));
        return $pdf->download('KHS_' . $mahasiswa->nim . '.pdf');
    }

    /**
     * Generate PDF untuk Transkrip Nilai.
     */
    public function cetakTranskrip()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }

        $krs = $mahasiswa->mataKuliahs()
            ->wherePivotNotNull('nilai')
            ->orderBy('semester')
            ->get();

        $krs_per_semester = $krs->groupBy('semester');

        // Logika perhitungan IPK
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

        $pdf = Pdf::loadView('transkrip.pdf', compact('mahasiswa', 'krs_per_semester', 'total_sks', 'ipk'));
        return $pdf->download('Transkrip_' . $mahasiswa->nim . '.pdf');
    }

    /**
     * Generate PDF untuk Kartu Rencana Studi (KRS).
     */
    public function cetakKrs()
    {
        $mahasiswa = Auth::user()->mahasiswa->load('dosenWali', 'programStudi.kaprodi');
        $krs = $mahasiswa->mataKuliahs;

        // Ambil data nama rektor dari database
        $rektor = Pengaturan::where('key', 'nama_rektor')->first();

        $pdf = Pdf::loadView('krs.pdf', compact('mahasiswa', 'krs', 'rektor'));
        return $pdf->download('KRS_' . $mahasiswa->nim . '.pdf');
    }
}