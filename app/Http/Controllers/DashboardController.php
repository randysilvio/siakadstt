<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pengumuman;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\Koleksi;
use App\Models\Pembayaran;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard yang sesuai berdasarkan peran pengguna.
     */
    public function index()
    {
        $user = Auth::user();

        $query = Pengumuman::latest()->take(5);
        if ($user->role !== 'admin') {
            $query->where('target_role', 'semua')
                  ->orWhere('target_role', $user->role);
        }
        $pengumumans = $query->get();

        switch ($user->role) {
            case 'admin':
                // --- START: KODE BARU UNTUK DATA GRAFIK ADMIN ---
                // Mengambil semua prodi dan menghitung jumlah mahasiswa terkait secara efisien
                $prodiData = ProgramStudi::withCount('mahasiswas')->get();

                // Memformat data agar bisa dibaca oleh Chart.js
                $prodiLabels = $prodiData->pluck('nama_prodi');
                $prodiCounts = $prodiData->pluck('mahasiswas_count');

                $dataGrafikProdi = [
                    'labels' => $prodiLabels,
                    'data' => $prodiCounts,
                ];
                // --- END: KODE BARU UNTUK DATA GRAFIK ADMIN ---

                return view('dashboard.admin', [
                    'totalMahasiswa' => Mahasiswa::count(),
                    'totalDosen' => Dosen::count(),
                    'totalProdi' => ProgramStudi::count(),
                    'totalMatkul' => MataKuliah::count(),
                    'pengumumans' => $pengumumans,
                    'dataGrafikProdi' => $dataGrafikProdi, // Kirim data grafik ke view
                ]);

            // ... (case 'dosen', 'mahasiswa', dan 'tendik' tetap sama, tidak perlu diubah)
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

                $ipsPerSemester = [];
                $krsDikelompokkan = $krs_selesai->groupBy('pivot.semester_diambil');
                for ($i = 1; $i <= 8; $i++) {
                    if (isset($krsDikelompokkan[$i])) {
                        $totalSksSemester = 0;
                        $totalBobotSemester = 0;
                        foreach ($krsDikelompokkan[$i] as $mk) {
                            if (isset($bobot_nilai[$mk->pivot->nilai])) {
                                $totalSksSemester += $mk->sks;
                                $totalBobotSemester += ($bobot_nilai[$mk->pivot->nilai] * $mk->sks);
                            }
                        }
                        $ips = ($totalSksSemester > 0) ? round($totalBobotSemester / $totalSksSemester, 2) : 0;
                        $ipsPerSemester[] = $ips;
                    } else {
                        $ipsPerSemester[] = 0;
                    }
                }
                
                $dataGrafik = [
                    'labels' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6', 'Sem 7', 'Sem 8'],
                    'data' => $ipsPerSemester,
                ];

                return view('dashboard', [
                    'mahasiswa' => $mahasiswa,
                    'ipk' => $ipk,
                    'total_sks' => $total_sks_lulus,
                    'memiliki_tagihan' => $mahasiswa->pembayarans()->where('status', 'belum_lunas')->exists(),
                    'pengumuman' => $pengumumans,
                    'dataGrafik' => $dataGrafik,
                ]);

            case 'tendik':
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

        return view('welcome');
    }
}