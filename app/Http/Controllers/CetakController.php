<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pengaturan;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;

class CetakController extends Controller
{
    /**
     * Generate PDF untuk Kartu Hasil Studi (KHS).
     */
    public function cetakKhs()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa;

        $krsPerTahunAkademik = $mahasiswa->mataKuliahs()
            ->withPivot('nilai', 'tahun_akademik_id')
            ->wherePivotNotNull('nilai')
            ->get()
            ->groupBy('pivot.tahun_akademik_id');

        $tahunAkademiks = TahunAkademik::find($krsPerTahunAkademik->keys());

        $pdf = Pdf::loadView('khs.pdf', compact('mahasiswa', 'krsPerTahunAkademik', 'tahunAkademiks'));
        return $pdf->download('KHS_' . $mahasiswa->nim . '.pdf');
    }

    /**
     * Generate PDF untuk Transkrip Nilai.
     */
    public function cetakTranskrip()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa;

        $krs = $mahasiswa->mataKuliahs()
                         ->wherePivotNotNull('nilai')
                         ->get();

        $krs_per_semester = $krs->groupBy('semester');
        $ipk = $mahasiswa->hitungIpk();
        $total_sks = $mahasiswa->totalSksLulus();

        $pdf = Pdf::loadView('transkrip.pdf', compact('mahasiswa', 'krs_per_semester', 'total_sks', 'ipk'));
        return $pdf->download('Transkrip_' . $mahasiswa->nim . '.pdf');
    }

    /**
     * Generate PDF untuk Kartu Rencana Studi (KRS).
     */
    public function cetakKrs()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa->load('dosenWali.user', 'programStudi.kaprodi.user');
        
        $tahunAkademik = TahunAkademik::where('is_active', true)->firstOrFail();

        $krs = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $tahunAkademik->id)
            ->get();

        $rektor = Pengaturan::where('key', 'nama_rektor')->first();

        $pdf = Pdf::loadView('krs.pdf', compact('mahasiswa', 'krs', 'rektor', 'tahunAkademik'));
        return $pdf->download('KRS_' . $mahasiswa->nim . '_' . $tahunAkademik->tahun . '.pdf');
    }
}
