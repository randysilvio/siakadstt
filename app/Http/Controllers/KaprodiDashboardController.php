<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgramStudi;
use App\Models\Mahasiswa;
use Illuminate\View\View;

class KaprodiDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        if (!$user->dosen) {
            abort(403, 'Akses ditolak. Data dosen tidak ditemukan untuk pengguna ini.');
        }

        $dosen = $user->dosen;
        
        // [PERBAIKAN] Mengganti firstOrFail() menjadi first() agar tidak melempar 404 Not Found
        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->first(); 

        if (!$programStudi) {
            abort(403, 'Akses ditolak. Akun dosen Anda belum ditautkan sebagai Ketua Program Studi pada data Master Program Studi. Silakan hubungi Administrator.');
        }

        // --- MENGHITUNG DATA UNTUK SMART FILTER TABS ---
        $countSemua = Mahasiswa::where('program_studi_id', $programStudi->id)->count();
        
        $countMenunggu = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_krs', 'Menunggu Persetujuan')->count();
                            
        $countDisetujui = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_krs', 'Disetujui')->count();
                            
        $countDitolak = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_krs', 'Ditolak')->count();
                            
        $countBelum = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where(function($q) {
                                $q->whereNull('status_krs')->orWhere('status_krs', '');
                            })->count();

        // --- QUERY UTAMA DAFTAR MAHASISWA ---
        $query = $programStudi->mahasiswas()->with('user');

        // Filter berdasarkan tombol status yang diklik
        $query->when($request->filled('status'), function ($q) use ($request) {
            if ($request->status == 'Belum') {
                $q->where(function($sub) {
                    $sub->whereNull('status_krs')->orWhere('status_krs', '');
                });
            } else {
                $q->where('status_krs', $request->status);
            }
        });

        // Filter pencarian ketikan
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nim', 'like', "%{$search}%");
            });
        });

        $mahasiswas = $query->paginate(10)->withQueryString();

        return view('kaprodi.dashboard', compact(
            'programStudi', 'mahasiswas', 
            'countSemua', 'countMenunggu', 'countDisetujui', 'countDitolak', 'countBelum'
        ));
    }
}