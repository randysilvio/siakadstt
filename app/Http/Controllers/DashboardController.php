<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Pengumuman;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role == 'admin') {
            $totalMahasiswa = Mahasiswa::count();
            $totalProdi = ProgramStudi::count();
            $totalMataKuliah = MataKuliah::count();
            $totalDosen = Dosen::count();

            $pengumumans = Pengumuman::where('target_role', 'admin')
                                     ->orWhere('target_role', 'semua')
                                     ->latest()->take(5)->get();

            return view('dashboard.admin', compact('totalMahasiswa', 'totalProdi', 'totalMataKuliah', 'totalDosen', 'pengumumans'));

        } elseif ($user->role == 'dosen') {
            return redirect()->route('dosen.dashboard');
            
        } elseif ($user->role == 'mahasiswa') {
            $mahasiswa = $user->mahasiswa;
            if (!$mahasiswa) {
                // Tambahkan penanganan jika data mahasiswa tidak ditemukan
                auth()->logout();
                return redirect()->route('login')->with('error', 'Data mahasiswa tidak ditemukan. Silakan hubungi admin.');
            }

            $memiliki_tagihan = $mahasiswa->pembayarans()->where('status', 'belum_lunas')->exists();
            
            $krs = $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->get();
            $total_sks = 0;
            $total_bobot_sks = 0;
            $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];
            foreach ($krs as $mk) {
                $sks = $mk->sks;
                $nilai = $mk->pivot->nilai;
                if (isset($bobot_nilai[$nilai])) {
                    $total_sks += $sks;
                    $total_bobot_sks += ($bobot_nilai[$nilai] * $sks);
                }
            }
            $ipk = ($total_sks > 0) ? round($total_bobot_sks / $total_sks, 2) : 0;

            $pengumumans = Pengumuman::where('target_role', 'mahasiswa')
                                     ->orWhere('target_role', 'semua')
                                     ->latest()->take(5)->get();

            return view('dashboard.mahasiswa', compact('mahasiswa', 'total_sks', 'ipk', 'memiliki_tagihan', 'pengumumans'));
        }

        // Fallback untuk user tanpa role yang sesuai
        return view('dashboard');
    }

    /**
     * Menyediakan data untuk grafik mahasiswa per prodi.
     */
    public function mahasiswaPerProdi()
    {
        $data = ProgramStudi::withCount('mahasiswas')->get();

        $labels = $data->pluck('nama_prodi');
        $values = $data->pluck('mahasiswas_count');

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }
}
