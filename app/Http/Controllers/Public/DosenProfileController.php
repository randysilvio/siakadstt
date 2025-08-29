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
        $query = Dosen::query()->with('user');

        // Logika untuk pencarian
        if ($request->filled('search')) {
            $query->where('nama_lengkap', 'like', '%' . $request->search . '%');
        }

        // Logika untuk filter program studi
        if ($request->filled('program_studi_id')) {
            // Ini asumsi relasi antara Dosen dan Program Studi. 
            // Jika Dosen tidak langsung terhubung ke Program Studi, logika ini perlu disesuaikan.
            // Untuk sekarang, kita asumsikan setiap dosen terhubung ke prodi melalui mata kuliah.
        }

        $dosens = $query->latest()->paginate(12);
        $program_studis = ProgramStudi::all();

        return view('public.dosen.index', compact('dosens', 'program_studis'));
    }

    /**
     * Menampilkan halaman profil detail seorang dosen.
     */
    public function show(Dosen $dosen)
    {
        // Memuat relasi user untuk mengambil data email, dll.
        $dosen->load('user', 'mataKuliahs');
        
        return view('public.dosen.show', compact('dosen'));
    }
}