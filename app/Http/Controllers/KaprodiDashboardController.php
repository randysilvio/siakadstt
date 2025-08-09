<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgramStudi;

class KaprodiDashboardController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;

        // Cari program studi yang dipimpin oleh dosen yang login
        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->withCount('mahasiswas')->firstOrFail();

        // Ambil daftar mahasiswa dari program studi tersebut
        $mahasiswas = $programStudi->mahasiswas()->with('user')->paginate(10);

        return view('kaprodi.dashboard', compact('programStudi', 'mahasiswas'));
    }
}