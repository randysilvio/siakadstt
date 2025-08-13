<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Pengumuman;
use App\Models\Pembayaran;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role;
        $jabatan = $user->jabatan;

        // Ambil pengumuman yang ditujukan untuk peran pengguna atau untuk semua user
        $pengumumans = Pengumuman::whereIn('target_role', [$role, 'semua'])->latest()->take(5)->get();

        if ($role == 'admin') {
            $totalMahasiswa = Mahasiswa::count();
            $totalProdi = ProgramStudi::count();
            $totalMataKuliah = MataKuliah::count();
            $totalDosen = Dosen::count();

            // Ambil data untuk grafik distribusi mahasiswa per prodi
            $mahasiswaPerProdi = Mahasiswa::select('program_studi_id', \DB::raw('count(*) as total'))
                ->groupBy('program_studi_id')
                ->with('programStudi')
                ->get();

            return view('dashboard.admin', compact('totalMahasiswa', 'totalProdi', 'totalMataKuliah', 'totalDosen', 'pengumumans', 'mahasiswaPerProdi'));

        } elseif ($role == 'dosen') {
            $dosen = $user->dosen;
            $mata_kuliahs = $dosen->mataKuliahs()->withCount('mahasiswas')->get();
            $jumlahMahasiswaWali = $dosen->mahasiswaWali()->count();
            $prodiYangDikepalai = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->first();
            
            return view('dashboard.dosen', compact('dosen', 'mata_kuliahs', 'jumlahMahasiswaWali', 'prodiYangDikepalai', 'pengumumans'));

        } elseif ($role == 'mahasiswa') {
            $mahasiswa = $user->mahasiswa;
            if (!$mahasiswa) {
                auth()->logout();
                return redirect()->route('login')->with('error', 'Data mahasiswa tidak ditemukan. Silakan hubungi admin.');
            }

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
            
            $memiliki_tagihan = $mahasiswa->pembayarans()->where('status', 'belum lunas')->exists();

            return view('dashboard.mahasiswa', compact('mahasiswa', 'total_sks', 'ipk', 'memiliki_tagihan', 'pengumumans'));
            
        } elseif ($role == 'tendik') {
            if ($jabatan == 'pustakawan') {
                return redirect()->route('perpustakaan.dashboard');
            } elseif ($jabatan == 'keuangan') {
                return redirect()->route('keuangan.dashboard');
            }
        }

        return view('dashboard.default', compact('pengumumans'));
    }

    // Metode ini digunakan untuk mengambil data chart via AJAX
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