<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pengumuman;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\Koleksi; // Diperlukan untuk dasbor perpustakaan
use App\Models\Pembayaran; // Diperlukan untuk dasbor keuangan

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard yang sesuai berdasarkan peran pengguna.
     * Controller ini sekarang menjadi pusat untuk semua logika dashboard,
     * menghilangkan redirect yang menyebabkan error dan notifikasi ganda.
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil pengumuman yang relevan
        $query = Pengumuman::latest()->take(5);
        if ($user->role !== 'admin') {
            $query->where('target_role', 'semua')
                  ->orWhere('target_role', $user->role);
        }
        $pengumumans = $query->get();

        // Logika untuk setiap peran
        switch ($user->role) {
            case 'admin':
                return view('dashboard.admin', [
                    'total_mahasiswa' => Mahasiswa::count(),
                    'total_dosen' => Dosen::count(),
                    'total_prodi' => ProgramStudi::count(),
                    'total_matkul' => MataKuliah::count(),
                    'pengumumans' => $pengumumans
                ]);

            case 'dosen':
                $dosen = $user->dosen;
                if (!$dosen) { abort(404, 'Data dosen tidak ditemukan.'); }
                return view('dosen.dashboard', [
                    'dosen' => $dosen,
                    'mata_kuliahs' => $dosen->mataKuliahs()->withCount('mahasiswas')->get(),
                    'jumlahMahasiswaWali' => $dosen->mahasiswaWali()->count(),
                    'prodiYangDikepalai' => ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->first(),
                    'pengumumans' => $pengumumans
                ]);

            case 'mahasiswa':
                $mahasiswa = $user->mahasiswa;
                if (!$mahasiswa) { abort(404, 'Data mahasiswa tidak ditemukan.'); }
                $krs_selesai = $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->get();
                $total_sks_lulus = 0;
                $total_bobot_sks = 0;
                $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];
                foreach ($krs_selesai as $mk) {
                    if (isset($bobot_nilai[$mk->pivot->nilai])) {
                        $total_sks_lulus += $mk->sks;
                        $total_bobot_sks += ($bobot_nilai[$mk->pivot->nilai] * $mk->sks);
                    }
                }
                $ipk = ($total_sks_lulus > 0) ? round($total_bobot_sks / $total_sks_lulus, 2) : 0;
                return view('dashboard.mahasiswa', [
                    'mahasiswa' => $mahasiswa,
                    'ipk' => $ipk,
                    'total_sks' => $total_sks_lulus,
                    'memiliki_tagihan' => $mahasiswa->pembayarans()->where('status', 'belum_lunas')->exists(),
                    'pengumumans' => $pengumumans
                ]);

            case 'tendik':
                // PERBAIKAN: Logika tendik sekarang menampilkan view secara langsung
                if ($user->jabatan == 'pustakawan') {
                    return view('perpustakaan.dashboard', [
                        'totalJudul' => Koleksi::count(),
                        'totalEksemplar' => Koleksi::sum('jumlah_stok'),
                        'pengumumans' => $pengumumans
                    ]);
                }
                if ($user->jabatan == 'keuangan') {
                    return view('pembayaran.dashboard', [
                        'totalTagihan' => Pembayaran::count(),
                        'totalLunas' => Pembayaran::where('status', 'lunas')->count(),
                        'totalBelumLunas' => Pembayaran::where('status', 'belum lunas')->count(),
                        'pengumumans' => $pengumumans
                    ]);
                }
                break;
        }

        // Fallback jika tidak ada peran yang cocok
        return view('welcome');
    }
}
