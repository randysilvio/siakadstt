<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgramStudi;
use Illuminate\View\View;

class KaprodiDashboardController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pastikan user memiliki relasi dosen
        if (!$user->dosen) {
            abort(403, 'Akses ditolak. Data dosen tidak ditemukan untuk pengguna ini.');
        }

        $dosen = $user->dosen;

        // Cari program studi yang dipimpin oleh dosen yang login
        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)
            ->withCount('mahasiswas')
            ->firstOrFail(); // Akan menghasilkan 404 jika tidak ditemukan

        // Ambil daftar mahasiswa dari program studi tersebut
        $mahasiswas = $programStudi->mahasiswas()->with('user')->paginate(10);

        return view('kaprodi.dashboard', compact('programStudi', 'mahasiswas'));
    }
}
