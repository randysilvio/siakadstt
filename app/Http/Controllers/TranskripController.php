<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TranskripController extends Controller
{
    public function index(): View | RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa;

        // 1. GATEKEEPER KHUSUS ALUMNI (STATUS LULUS): Wajib isi Tracer Study sebelum membuka Transkrip Nilai Akhir
        if ($mahasiswa->status_mahasiswa === 'Lulus' && !$mahasiswa->sudah_isi_tracer) {
            return redirect()->route('tracer.index')
                ->with('error', 'Selamat atas kelulusan Anda! Mohon lengkapi kuesioner Tracer Study Alumni terlebih dahulu sebelum mengakses dan mengunduh Transkrip Nilai Akhir.');
        }

        // 2. GATEKEEPER MAHASISWA AKTIF: Wajib menyelesaikan EDOM semester berjalan
        if ($mahasiswa->status_mahasiswa !== 'Lulus' && $mahasiswa->status_evaluasi === 'belum_isi') {
            return redirect()->route('evaluasi.index')
                ->with('error', 'PERHATIAN: Anda wajib menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM) semester ini sebelum dapat mengakses halaman Transkrip Nilai.');
        }

        [
            'krs_per_semester' => $krs_per_semester,
            'total_sks'        => $total_sks,
            'ipk'              => $ipk,
        ] = app(\App\Services\RekapNilaiService::class)->transkrip($mahasiswa);

        return view('transkrip.index', compact('mahasiswa', 'krs_per_semester', 'total_sks', 'ipk'));
    }
}