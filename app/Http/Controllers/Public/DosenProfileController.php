<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;

class DosenProfileController extends Controller
{
    /**
     * Menampilkan halaman direktori daftar semua dosen.
     */
    public function index(Request $request)
    {
        // Memuat relasi user dan prodi agar performa query lebih cepat
        $query = Dosen::query()->with(['user', 'programStudi']);

        // Logika untuk pencarian teks (Nama)
        if ($request->filled('search')) {
            $query->where('nama_lengkap', 'like', '%' . $request->search . '%');
        }

        // Logika untuk filter berdasarkan Program Studi
        if ($request->filled('program_studi_id')) {
            $query->where('program_studi_id', $request->program_studi_id);
        }

        // withQueryString() menjaga filter agar tidak hilang saat pindah halaman (pagination)
        $dosens = $query->latest()->paginate(12)->withQueryString();
        
        $program_studis = ProgramStudi::orderBy('nama_prodi', 'asc')->get();

        return view('public.dosen.index', compact('dosens', 'program_studis'));
    }

    /**
     * Menampilkan halaman profil detail seorang dosen.
     */
    public function show(Dosen $dosen)
    {
        // Memuat relasi lengkap untuk halaman detail
        $dosen->load(['user', 'mataKuliahs', 'programStudi']);
        
        return view('public.dosen.show', compact('dosen'));
    }
}