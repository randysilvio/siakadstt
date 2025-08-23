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
        $pengumumans = Pengumuman::latest()->take(5)->get();
        $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        // ==================================================================
        // PERBAIKAN UTAMA: Prioritaskan Dasbor Dosen
        // Jika pengguna memiliki peran 'dosen', selalu tampilkan dasbor ini
        // terlepas dari peran lain yang mungkin mereka miliki (Rektorat, Kaprodi, dll).
        // ==================================================================
        if ($user->hasRole('dosen')) {
            $dosen = $user->dosen;
            if (!$dosen) { abort(404, 'Data dosen tidak ditemukan untuk akun ini.'); }
            
            $jadwalKuliahDosen = Jadwal::whereHas('mataKuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->id);
            })
            ->with('mataKuliah')
            ->get()
            ->sortBy(fn($jadwal) => $hariOrder[$jadwal->hari] ?? 99);
            
            // Data dinamis untuk peran tambahan (Kaprodi)
            $dataKaprodi = null;
            if ($user->hasRole('kaprodi')) {
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

            // Menggunakan view 'dosen.dashboard' yang sudah ada
            return view('dosen.dashboard', [
                'dosen' => $dosen,
                'mata_kuliahs' => $dosen->mataKuliahs()->withCount('mahasiswas')->get(),
                'jumlahMahasiswaWali' => $dosen->mahasiswaWali()->count(),
                'prodiYangDikepalai' => $prodiYangDikepalai ?? null, // dari logika di atas
                'dataKaprodi' => $dataKaprodi, // data tambahan untuk view
                'pengumumans' => $pengumumans,
                'jadwalKuliah' => $jadwalKuliahDosen,
            ]);
        }
        // Pengecekan peran lain hanya berjalan jika user BUKAN seorang dosen
        elseif ($user->hasRole('admin')) {
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
        elseif ($user->hasRole('rektorat')) {
            return redirect()->route('rektorat.dashboard');
        }
        elseif ($user->hasRole('penjaminan_mutu')) {
            return redirect()->route('mutu.dashboard');
        }
        elseif ($user->hasRole('mahasiswa')) {
            $mahasiswa = $user->mahasiswa;
            if (!$mahasiswa) { abort(404, 'Data mahasiswa tidak ditemukan untuk akun ini.'); }

            $jadwalKuliahMahasiswa = Jadwal::whereHas('mataKuliah.mahasiswas', function ($query) use ($mahasiswa, $periodeAktif) {
                $query->where('mahasiswas.id', $mahasiswa->id)
                      ->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeAktif?->id);
            })
            ->with('mataKuliah.dosen')
            ->get()
            ->sortBy(fn($jadwal) => $hariOrder[$jadwal->hari] ?? 99);

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

            $krsDikelompokkan = $krs_selesai->groupBy('pivot.tahun_akademik_id');
            $tahunAkademikData = TahunAkademik::whereIn('id', $krsDikelompokkan->keys())->orderBy('id')->get()->keyBy('id');
            $ipsPerSemester = [];
            $labels = [];

            foreach ($tahunAkademikData as $id => $ta) {
                if(isset($krsDikelompokkan[$id])) {
                    $labels[] = $ta->nama; // Menggunakan nama dari model TahunAkademik
                    $krsGroup = $krsDikelompokkan[$id];
                    $totalSksSemester = 0;
                    $totalBobotSemester = 0;
                    foreach ($krsGroup as $mk) {
                        if (isset($bobot_nilai[$mk->pivot->nilai])) {
                            $totalSksSemester += $mk->sks;
                            $totalBobotSemester += ($bobot_nilai[$mk->pivot->nilai] * $mk->sks);
                        }
                    }
                    $ipsPerSemester[] = ($totalSksSemester > 0) ? round($totalBobotSemester / $totalSksSemester, 2) : 0;
                }
            }
            
            $dataGrafik = [
                'labels' => $labels,
                'data' => $ipsPerSemester,
            ];

            $periodeKrsAktif = $periodeAktif && Carbon::now()->between($periodeAktif->tanggal_mulai_krs, Carbon::parse($periodeAktif->tanggal_selesai_krs)->endOfDay());
            
            // Logika untuk periode evaluasi
            $periodeEvaluasiAktif = \App\Models\EvaluasiSesi::where('is_active', true)
                ->where('tanggal_mulai', '<=', Carbon::now())
                ->where('tanggal_selesai', '>=', Carbon::now())
                ->exists();

            return view('dashboard', [
                'mahasiswa' => $mahasiswa,
                'ipk' => $ipk,
                'total_sks' => $total_sks_lulus,
                'memiliki_tagihan' => $mahasiswa->pembayarans()->where('status', 'belum_lunas')->exists(),
                'pengumuman' => $pengumumans,
                'dataGrafik' => $dataGrafik,
                'jadwalKuliah' => $jadwalKuliahMahasiswa,
                'periodeKrsAktif' => $periodeKrsAktif,
                'periodeEvaluasiAktif' => $periodeEvaluasiAktif,
            ]);
        }
        elseif ($user->hasRole('keuangan')) {
            return view('pembayaran.dashboard', [
                'totalTagihan' => Pembayaran::count(),
                'totalLunas' => Pembayaran::where('status', 'lunas')->count(),
                'totalBelumLunas' => Pembayaran::where('status', 'belum_lunas')->count(),
                'pengumumans' => $pengumumans
            ]);
        }
        elseif ($user->hasRole('pustakawan')) {
            return view('perpustakaan.dashboard', [
                'totalJudul' => Koleksi::count(),
                'totalEksemplar' => Koleksi::sum('jumlah_stok'),
                'pengumumans' => $pengumumans
            ]);
        }

        // Fallback jika user tidak punya peran yang dikenali
        Auth::logout();
        return redirect('/login')->with('error', 'Peran Anda tidak dikenali atau belum diatur. Silakan hubungi administrator.');
    }
}
