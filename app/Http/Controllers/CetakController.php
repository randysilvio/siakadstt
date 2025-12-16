<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pengaturan;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\Jadwal; // Import Model Jadwal

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

    /**
     * [BARU] Generate PDF Jadwal Kuliah Semester Ini
     */
    public function cetakJadwal()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa->load('programStudi');
        
        $tahunAkademik = TahunAkademik::where('is_active', true)->firstOrFail();

        // 1. Ambil ID Mata Kuliah yang diambil mahasiswa semester ini
        $mataKuliahIds = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $tahunAkademik->id)
            ->pluck('mata_kuliahs.id');

        // 2. Ambil Jadwal berdasarkan mata kuliah tersebut
        $jadwals = Jadwal::whereIn('mata_kuliah_id', $mataKuliahIds)
            ->with(['mataKuliah', 'mataKuliah.dosen'])
            ->get()
            ->sortBy(function($jadwal) {
                // Urutkan berdasarkan Hari (Senin=1, ..., Minggu=7)
                $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
                return $hariOrder[$jadwal->hari] ?? 99;
            });

        $pdf = Pdf::loadView('mahasiswa.cetak_jadwal', compact('mahasiswa', 'jadwals', 'tahunAkademik'));
        
        // Stream agar bisa dipreview dulu di browser
        return $pdf->stream('Jadwal_Kuliah_' . $mahasiswa->nim . '.pdf');
    }
}