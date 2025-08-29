<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\TahunAkademik;

class KhsController extends Controller
{
    /**
     * Menampilkan Kartu Hasil Studi (KHS) mahasiswa yang sedang login.
     * Data dikelompokkan per tahun akademik.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pastikan pengguna memiliki data mahasiswa terkait
        if (!$user->mahasiswa) {
            abort(403, 'Hanya mahasiswa yang dapat mengakses halaman ini.');
        }
        $mahasiswa = $user->mahasiswa;

        // 1. Ambil semua mata kuliah yang sudah dinilai
        // 2. Kelompokkan berdasarkan ID tahun akademik dari tabel pivot
        $krsPerTahunAkademik = $mahasiswa->mataKuliahs()
            ->withPivot('nilai', 'tahun_akademik_id')
            ->wherePivotNotNull('nilai')
            ->get()
            ->groupBy('pivot.tahun_akademik_id');

        // 3. Ambil data model TahunAkademik berdasarkan ID yang ada di KHS
        //    Ini digunakan untuk menampilkan nama tahun dan semester di view
        $tahunAkademiks = TahunAkademik::find($krsPerTahunAkademik->keys());

        // 4. Kirim semua data yang dibutuhkan ke view
        return view('khs.index', compact(
            'mahasiswa',
            'krsPerTahunAkademik',
            'tahunAkademiks'
        ));
    }
}