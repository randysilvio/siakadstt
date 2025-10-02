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
use App\Models\Jadwal;
use App\Models\TahunAkademik;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard yang sesuai berdasarkan peran pengguna.
     */
    public function index()
    {
        $user = Auth::user();
        
        $userRoles = $user->roles->pluck('name')->toArray();
        array_push($userRoles, 'semua');

        $pengumumans = Pengumuman::whereIn('target_role', $userRoles)
            ->latest()
            ->take(5)
            ->get();

        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        if ($user->hasRole('admin')) {
            $prodiData = ProgramStudi::withCount('mahasiswas')->get();
            $dataGrafikProdi = [
                'labels' => $prodiData->pluck('nama_prodi'),
                'data' => $prodiData->pluck('mahasiswas_count'),
            ];
            return view('dashboard.admin', [
                'totalMahasiswa' => Mahasiswa::count(),
                'totalDosen' => Dosen::count(),
                'totalProdi' => ProgramStudi::count(),
                'totalMatkul' => MataKuliah::count(),
                'pengumumans' => $pengumumans,
                'dataGrafikProdi' => $dataGrafikProdi,
            ]);
        }
        elseif ($user->hasRole('dosen') || $user->isKaprodi()) {
            $dosen = $user->dosen;
            if (!$dosen) {
                abort(404, 'Data dosen tidak ditemukan untuk akun ini.');
            }
        
            $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
            $jadwalKuliahDosen = Jadwal::whereHas('mataKuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->id);
            })
            ->with('mataKuliah')
            ->get()
            ->sortBy(fn($jadwal) => $hariOrder[$jadwal->hari] ?? 99);
            
            $dataKaprodi = null;
            if ($user->isKaprodi()) {
                $prodiYangDikepalai = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->first();
                if($prodiYangDikepalai) {
                    $krsMenungguPersetujuan = Mahasiswa::where('program_studi_id', $prodiYangDikepalai->id)
                                                    ->where('status_krs', 'Menunggu Persetujuan')
                                                    ->count();
                    $dataKaprodi = [
                        'prodi' => $prodiYangDikepalai,
                        'krs_count' => $krsMenungguPersetujuan
                    ];
                }
            }

            return view('dosen.dashboard', [
                'dosen' => $dosen,
                'mata_kuliahs' => $dosen->mataKuliahs()->withCount('mahasiswas')->get(),
                'jumlahMahasiswaWali' => $dosen->mahasiswaWali()->count(),
                'dataKaprodi' => $dataKaprodi,
                'pengumumans' => $pengumumans,
                'jadwalKuliah' => $jadwalKuliahDosen,
            ]);
        }
        elseif ($user->hasRole('mahasiswa')) {
            $mahasiswa = $user->mahasiswa;
            if (!$mahasiswa) { abort(404, 'Data mahasiswa tidak ditemukan untuk akun ini.'); }

            $jadwalKuliahMahasiswa = collect();
            if ($mahasiswa->status_krs === 'Disetujui' && $periodeAktif) {
                $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
                
                $mataKuliahIds = $mahasiswa->mataKuliahs()
                    ->wherePivot('tahun_akademik_id', $periodeAktif->id)
                    ->pluck('mata_kuliahs.id');

                if ($mataKuliahIds->isNotEmpty()) {
                    $jadwalKuliahMahasiswa = Jadwal::whereIn('mata_kuliah_id', $mataKuliahIds)
                        ->with('mataKuliah.dosen')
                        ->get()
                        ->sortBy(fn($jadwal) => $hariOrder[$jadwal->hari] ?? 99);
                }
            }

            return view('dashboard.mahasiswa', [
                'mahasiswa' => $mahasiswa,
                'ipk' => $mahasiswa->hitungIpk(),
                'total_sks' => $mahasiswa->totalSksLulus(),
                'memiliki_tagihan' => $mahasiswa->pembayarans()->where('status', 'belum_lunas')->exists(),
                'pengumumans' => $pengumumans,
                'jadwalKuliah' => $jadwalKuliahMahasiswa,
            ]);
        }
        elseif ($user->hasRole('keuangan')) {
            // --- PERBAIKAN: Menambahkan data yang akan dikirim ke view ---
            $pembayaranTerbaru = Pembayaran::where('status', 'lunas')
                ->with('mahasiswa')
                ->latest('tanggal_bayar')
                ->take(5)
                ->get();

            $tagihanTerlama = Pembayaran::where('status', 'belum_lunas')
                ->with('mahasiswa')
                ->orderBy('created_at', 'asc')
                ->take(5)
                ->get();

            return view('pembayaran.dashboard', [
                'totalTagihan' => Pembayaran::count(),
                'totalLunas' => Pembayaran::where('status', 'lunas')->count(),
                'totalBelumLunas' => Pembayaran::where('status', 'belum_lunas')->count(),
                'pengumumans' => $pengumumans,
                'pembayaranTerbaru' => $pembayaranTerbaru,
                'tagihanTerlama' => $tagihanTerlama
            ]);
        }
        elseif ($user->hasRole('pustakawan')) {
            return view('perpustakaan.dashboard', [
                'totalJudul' => Koleksi::count(),
                'totalEksemplar' => Koleksi::sum('jumlah_stok'),
                'pengumumans' => $pengumumans
            ]);
        }
         elseif ($user->hasRole('rektorat')) {
            return redirect()->route('rektorat.dashboard');
        }
        elseif ($user->hasRole('penjaminan_mutu')) {
            return redirect()->route('mutu.dashboard');
        }

        Auth::logout();
        return redirect('/login')->with('error', 'Peran Anda tidak dikenali atau belum diatur. Silakan hubungi administrator.');
    }
}