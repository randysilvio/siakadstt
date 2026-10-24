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
        $query = Dosen::query()->with(['user', 'programStudi']);

        if ($request->filled('search')) {
            $query->where('nama_lengkap', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('program_studi_id')) {
            $query->where('program_studi_id', $request->program_studi_id);
        }

        $dosens = $query->latest()->paginate(12)->withQueryString();
        $program_studis = ProgramStudi::orderBy('nama_prodi', 'asc')->get();

        return view('public.dosen.index', compact('dosens', 'program_studis'));
    }

    /**
     * Menampilkan halaman profil detail seorang dosen beserta Portofolio Tridharma.
     */
    public function show(Dosen $dosen)
    {
        $dosen->load([
            'user',
            'mataKuliahs',
            'programStudi',
            'penelitians',
            'pengabdians',
        ]);
        
        return view('public.dosen.show', compact('dosen'));
    }
}