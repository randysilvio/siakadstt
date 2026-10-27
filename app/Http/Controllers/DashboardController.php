<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; 
use Illuminate\Support\Facades\Schema;
use App\Models\Pengumuman;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\Koleksi;
use App\Models\Pembayaran;
use App\Models\Jadwal;
use App\Models\TahunAkademik;
use App\Models\Peminjaman; 
use App\Models\SuratKeputusan;
use App\Models\DokumenPublik;
use App\Models\User;
use App\Models\Role;
use App\Models\VerumKelas;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard yang sesuai berdasarkan peran pengguna.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // =========================================================
        // AUTO-RECOVERY (SELF-HEALING) ROLE CAMABA / MAHASISWA / DOSEN
        // =========================================================
        if (!$user->hasRole('camaba') && $user->camaba && !$user->camaba->is_migrated && !$user->mahasiswa) {
            $roleCamaba = Role::firstOrCreate(
                ['name' => 'camaba'],
                ['display_name' => 'Calon Mahasiswa Baru (PMB)']
            );
            $user->roles()->syncWithoutDetaching([$roleCamaba->id]);
            $user->load('roles');
        } elseif (!$user->hasRole('mahasiswa') && $user->mahasiswa) {
            $roleMhs = Role::firstOrCreate(
                ['name' => 'mahasiswa'],
                ['display_name' => 'Mahasiswa']
            );
            $user->roles()->syncWithoutDetaching([$roleMhs->id]);
            $user->load('roles');
        } elseif (!$user->hasRole('dosen') && $user->dosen) {
            $roleDosen = Role::firstOrCreate(
                ['name' => 'dosen'],
                ['display_name' => 'Dosen']
            );
            $user->roles()->syncWithoutDetaching([$roleDosen->id]);
            $user->load('roles');
        }

        // =========================================================
        // Dashboard Khusus CAMABA (Calon Mahasiswa Baru)
        // =========================================================
        if ($user->hasRole('camaba') || ($user->camaba && !$user->camaba->is_migrated)) {
            $camaba = $user->camaba; 
            
            $tagihan = Pembayaran::where('user_id', $user->id)
                ->where('jenis_pembayaran', 'formulir_pmb')
                ->latest()
                ->first();
            
            return view('dashboard.camaba', compact('camaba', 'tagihan'));
        }
        
        $userRoles = $user->roles->pluck('name')->toArray();
        array_push($userRoles, 'semua');

        $pengumumans = Pengumuman::whereIn('target_role', $userRoles)
            ->latest()
            ->take(5)
            ->get();

        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        // =========================================================
        // Dashboard Khusus ADMIN
        // =========================================================
        if ($user->hasRole('admin')) {
            $prodiData = ProgramStudi::withCount(['mahasiswas' => function ($q) {
                $q->where('status_mahasiswa', 'Aktif');
            }])->get();

            $dataGrafikProdi = [
                'labels' => $prodiData->pluck('nama_prodi'),
                'data'   => $prodiData->pluck('mahasiswas_count'),
            ];

            $onlineUsersMap = Cache::get('siakad-online-users-list', []);
            $onlineUserIds = array_keys($onlineUsersMap);
            $onlineUserIds = array_diff($onlineUserIds, [$user->id]); 

            if (!empty($onlineUserIds)) {
                $logPengguna = User::with('roles')
                    ->whereIn('id', $onlineUserIds)
                    ->get()
                    ->map(function ($u) use ($onlineUsersMap) {
                        $u->updated_at = Carbon::createFromTimestamp($onlineUsersMap[$u->id]);
                        return $u;
                    })
                    ->sortByDesc('updated_at')
                    ->take(5);
            } else {
                $logPengguna = User::with('roles')
                    ->where('id', '!=', $user->id)
                    ->orderBy('updated_at', 'desc')
                    ->take(5)
                    ->get();
            }

            $matkulBelumDiplot = 0;
            $persenKrs = 0;
            
            if ($periodeAktif) {
                $totalMhsAktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
                $krsDisetujui = Mahasiswa::where('status_mahasiswa', 'Aktif')
                    ->where('status_krs', 'Disetujui')
                    ->whereHas('mataKuliahs', function ($q) use ($periodeAktif) {
                        $q->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeAktif->id);
                    })
                    ->count();

                $persenKrs = $totalMhsAktif > 0 ? round(($krsDisetujui / $totalMhsAktif) * 100) : 0;
                $matkulBelumDiplot = MataKuliah::whereNull('dosen_id')->count();
            }

            $jadwalHariIni = collect();
            $mapHari = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            $hariIniTxt = $mapHari[date('l')];

            if ($periodeAktif) {
                $mkAktifSaatIniIds = DB::table('mahasiswa_mata_kuliah')
                    ->where('tahun_akademik_id', $periodeAktif->id)
                    ->distinct()
                    ->pluck('mata_kuliah_id')
                    ->toArray();

                if (!empty($mkAktifSaatIniIds)) {
                    $verumKelasMap = VerumKelas::where('tahun_akademik_id', $periodeAktif->id)
                        ->whereIn('mata_kuliah_id', $mkAktifSaatIniIds)
                        ->get()
                        ->keyBy('mata_kuliah_id');

                    $jadwalHariIni = Jadwal::with(['mataKuliah.dosen'])
                        ->whereIn('mata_kuliah_id', $mkAktifSaatIniIds)
                        ->where('hari', $hariIniTxt)
                        ->get()
                        ->filter(function ($jadwal) use ($verumKelasMap) {
                            $ruang = strtolower($jadwal->ruang ?? '');
                            $isVirtualRoom = str_contains($ruang, 'zoom') || str_contains($ruang, 'meet') || str_contains($ruang, 'http') || str_contains($ruang, 'virtual') || str_contains($ruang, 'online');
                            $hasVerum = $verumKelasMap->has($jadwal->mata_kuliah_id);
                            
                            return $isVirtualRoom || $hasVerum;
                        })
                        ->sortBy('jam_mulai')
                        ->map(function ($jadwal) use ($verumKelasMap) {
                            $verum = $verumKelasMap->get($jadwal->mata_kuliah_id);
                            $isMeetingActive = $verum ? $verum->is_meeting_active : false;

                            try {
                                $now = now();
                                $mulai = Carbon::parse($jadwal->jam_mulai);
                                $selesai = Carbon::parse($jadwal->jam_selesai);

                                if ($isMeetingActive) {
                                    $jadwal->ikon_status = 'bi-camera-video-fill text-danger pulse-badge'; 
                                    $jadwal->tooltip_status = 'Video Conference Sedang LIVE';
                                } elseif ($now->between($mulai, $selesai)) {
                                    $jadwal->ikon_status = 'bi-broadcast text-primary pulse-badge'; 
                                    $jadwal->tooltip_status = 'Sesi Online Sedang Berjalan';
                                } elseif ($now->lt($mulai)) {
                                    $jadwal->ikon_status = 'bi-clock-history text-secondary';
                                    $jadwal->tooltip_status = 'Menunggu Jadwal';
                                } else {
                                    $jadwal->ikon_status = 'bi-check2-circle text-dark';
                                    $jadwal->tooltip_status = 'Selesai';
                                }
                            } catch (\Exception $e) {
                                $jadwal->ikon_status = 'bi-exclamation-circle text-muted';
                                $jadwal->tooltip_status = 'Waktu Invalid';
                            }
                            return $jadwal;
                        });
                }
            }

            return view('dashboard.admin', [
                'totalMahasiswa'    => Mahasiswa::where('status_mahasiswa', 'Aktif')->count(),
                'totalDosen'        => Dosen::count(),
                'totalProdi'        => ProgramStudi::count(),
                'totalMatkul'       => MataKuliah::count(),
                'pengumumans'       => $pengumumans,
                'dataGrafikProdi'   => $dataGrafikProdi,
                'periodeAktif'      => $periodeAktif,
                'logPengguna'       => $logPengguna,
                'persenKrs'         => $persenKrs,
                'matkulBelumDiplot' => $matkulBelumDiplot,
                'jadwalHariIni'     => $jadwalHariIni, 
                'namaHariIni'       => $hariIniTxt,
            ]);
        }
        // =========================================================
        // Dashboard Khusus DOSEN
        // =========================================================
        elseif ($user->hasRole('dosen') || $user->isKaprodi()) {
            return app(\App\Http\Controllers\DosenDashboardController::class)->index($request);
        }
        // =========================================================
        // Dashboard Khusus MAHASISWA / ALUMNI
        // =========================================================
        elseif ($user->hasRole('mahasiswa')) {
            $mahasiswa = $user->mahasiswa;
            if (!$mahasiswa) {
                abort(404, 'Data mahasiswa tidak ditemukan untuk akun ini.');
            }

            $jadwalKuliahMahasiswa = collect();
            if ($mahasiswa->status_mahasiswa === 'Aktif' && $mahasiswa->status_krs === 'Disetujui' && $periodeAktif) {
                $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
                
                $krsDiambil = $mahasiswa->mataKuliahs()
                    ->wherePivot('tahun_akademik_id', $periodeAktif->id)
                    ->get();

                $mataKuliahIds = $krsDiambil->pluck('id')->toArray();
                $kelasMap = [];
                foreach ($krsDiambil as $mkItem) {
                    $kelasMap[$mkItem->id] = strtoupper($mkItem->pivot->nama_kelas ?? 'A');
                }

                if (!empty($mataKuliahIds)) {
                    $jadwalKuliahMahasiswa = Jadwal::whereIn('mata_kuliah_id', $mataKuliahIds)
                        ->with('mataKuliah.dosen')
                        ->get()
                        ->filter(function ($jadwal) use ($kelasMap) {
                            $klsMhs = $kelasMap[$jadwal->mata_kuliah_id] ?? 'A';
                            return strtoupper($jadwal->nama_kelas ?? 'A') === $klsMhs;
                        })
                        ->sortBy(fn($jadwal) => $hariOrder[$jadwal->hari] ?? 99)
                        ->values();
                }
            }

            return view('dashboard.mahasiswa', [
                'mahasiswa'        => $mahasiswa,
                'ipk'              => $mahasiswa->hitungIpk(),
                'total_sks'        => $mahasiswa->totalSksLulus(),
                'memiliki_tagihan' => $mahasiswa->pembayarans()->whereIn('status', ['belum_lunas', 'belum_bayar'])->exists(),
                'pengumumans'      => $pengumumans,
                'jadwalKuliah'     => $jadwalKuliahMahasiswa,
            ]);
        }
        // =========================================================
        // Dashboard Khusus KEUANGAN
        // =========================================================
        elseif ($user->hasRole('keuangan')) {
            $pembayaranTerbaru = Pembayaran::where('status', 'lunas')
                ->with(['mahasiswa', 'user'])
                ->latest('tanggal_bayar')
                ->take(5)
                ->get();

            // Prioritaskan tagihan 'menunggu_konfirmasi' (mahasiswa baru upload bukti bayar) di urutan paling atas
            $tagihanTerlama = Pembayaran::whereIn('status', ['menunggu_konfirmasi', 'belum_lunas', 'belum_bayar'])
                ->with(['mahasiswa', 'user'])
                ->orderByRaw("CASE WHEN status = 'menunggu_konfirmasi' THEN 0 ELSE 1 END")
                ->orderBy('updated_at', 'desc')
                ->take(8)
                ->get();

            return view('pembayaran.dashboard', [
                'totalTagihan'      => Pembayaran::count(),
                'totalLunas'        => Pembayaran::where('status', 'lunas')->count(),
                'totalBelumLunas'   => Pembayaran::whereIn('status', ['menunggu_konfirmasi', 'belum_lunas', 'belum_bayar'])->count(),
                'pengumumans'       => $pengumumans,
                'pembayaranTerbaru' => $pembayaranTerbaru,
                'tagihanTerlama'    => $tagihanTerlama
            ]);
        }
        // =========================================================
        // Dashboard Khusus PUSTAKAWAN
        // =========================================================
        elseif ($user->hasRole('pustakawan')) {
            $peminjamanTerlambat = Peminjaman::with(['koleksi', 'user'])
                ->where('status', 'Dipinjam')
                ->whereDate('jatuh_tempo', '<', Carbon::today())
                ->get();

            $koleksiPopuler = Koleksi::withCount('peminjamans')
                ->orderByDesc('peminjamans_count')
                ->take(5)
                ->get();

            return view('perpustakaan.dashboard', [
                'totalJudul'          => Koleksi::count(),
                'totalEksemplar'      => Koleksi::sum('jumlah_stok'),
                'peminjamanAktif'     => Peminjaman::where('status', 'Dipinjam')->count(),
                'terlambatCount'      => $peminjamanTerlambat->count(),
                'peminjamanTerlambat' => $peminjamanTerlambat,
                'aktivitasTerakhir'   => Peminjaman::with(['user', 'koleksi'])->latest()->take(5)->get(),
                'koleksiPopuler'      => $koleksiPopuler,
                'pengumumans'         => $pengumumans
            ]);
        }
        // =========================================================
        // Dashboard Khusus ADMINISTRASI UMUM
        // =========================================================
        elseif ($user->hasRole('administrasi_umum')) {
            $suratSelesai = SuratKeputusan::where('status', 'Selesai')->count();
            $suratMenunggu = SuratKeputusan::where('status', 'Menunggu Tanda Tangan')->count();
            $suratDraf = SuratKeputusan::where('status', 'Draf')->count();
            $totalDokumen = DokumenPublik::count();

            $suratTerbaru = SuratKeputusan::latest()->take(5)->get();
            $dokumenTerbaru = DokumenPublik::latest()->take(5)->get();

            return view('administrasi.dashboard', [
                'suratSelesai'   => $suratSelesai,
                'suratMenunggu'  => $suratMenunggu,
                'suratDraf'      => $suratDraf,
                'totalDokumen'   => $totalDokumen,
                'suratTerbaru'   => $suratTerbaru,
                'dokumenTerbaru' => $dokumenTerbaru,
                'pengumumans'    => $pengumumans
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

    public function mahasiswaPerProdi()
    {
        $prodiData = ProgramStudi::withCount(['mahasiswas' => function ($q) {
            $q->where('status_mahasiswa', 'Aktif');
        }])->orderBy('nama_prodi')->get();

        return response()->json([
            'labels' => $prodiData->pluck('nama_prodi'),
            'data'   => $prodiData->pluck('mahasiswas_count'),
            'items'  => $prodiData->map(function ($prodi) {
                return [
                    'id'         => $prodi->id,
                    'nama_prodi' => $prodi->nama_prodi,
                    'total'      => $prodi->mahasiswas_count,
                ];
            }),
        ]);
    }
}