<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; 
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
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard yang sesuai berdasarkan peran pengguna.
     */
    public function index()
    {
        $user = Auth::user();

        // =========================================================
        // Dashboard Khusus CAMABA (Calon Mahasiswa)
        // =========================================================
        if ($user->hasRole('camaba')) {
            $camaba = $user->camaba; 
            
            $tagihan = Pembayaran::where('user_id', $user->id)
                            ->where('jenis_pembayaran', 'formulir_pmb')
                            ->latest()
                            ->first();
            
            return view('dashboard.camaba', compact('camaba', 'tagihan'));
        }
        // =========================================================
        
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
            $prodiData = ProgramStudi::withCount('mahasiswas')->get();
            $dataGrafikProdi = [
                'labels' => $prodiData->pluck('nama_prodi'),
                'data' => $prodiData->pluck('mahasiswas_count'),
            ];

            // 1. DATA LOG PENGGUNA TERAKHIR
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

            // 2. DATA PROGRES SEMESTER (Smart Checklist)
            $matkulBelumDiplot = 0;
            $persenKrs = 0;
            
            if ($periodeAktif) {
                $totalMhs = Mahasiswa::count();
                $krsDisetujui = Mahasiswa::where('status_krs', 'Disetujui')->count();
                $persenKrs = $totalMhs > 0 ? round(($krsDisetujui / $totalMhs) * 100) : 0;
                $matkulBelumDiplot = MataKuliah::whereNull('dosen_id')->count();
            }

            // 3. PANTAUAN KELAS LIVE HARI INI
            $jadwalHariIni = collect();
            
            // Konversi nama hari bahasa Inggris ke Indonesia untuk pencarian di DB
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
                    $jadwalHariIni = Jadwal::with(['mataKuliah.dosen'])
                        ->whereIn('mata_kuliah_id', $mkAktifSaatIniIds)
                        ->where('hari', $hariIniTxt)
                        ->orderBy('jam_mulai', 'asc')
                        ->get()
                        ->map(function($jadwal) {
                            try {
                                $now = now();
                                $mulai = Carbon::parse($jadwal->jam_mulai);
                                $selesai = Carbon::parse($jadwal->jam_selesai);

                                if ($now->between($mulai, $selesai)) {
                                    $jadwal->status_kelas = 'LIVE SEDANG BERJALAN';
                                    $jadwal->warna_status = 'danger'; 
                                } elseif ($now->lt($mulai)) {
                                    $jadwal->status_kelas = 'MENUNGGU WAKTU';
                                    $jadwal->warna_status = 'secondary';
                                } else {
                                    $jadwal->status_kelas = 'KULIAH SELESAI';
                                    $jadwal->warna_status = 'success';
                                }
                            } catch (\Exception $e) {
                                $jadwal->status_kelas = 'WAKTU INVALID';
                                $jadwal->warna_status = 'dark';
                            }
                            return $jadwal;
                        });
                }
            }

            return view('dashboard.admin', [
                'totalMahasiswa' => Mahasiswa::count(),
                'totalDosen' => Dosen::count(),
                'totalProdi' => ProgramStudi::count(),
                'totalMatkul' => MataKuliah::count(),
                'pengumumans' => $pengumumans,
                'dataGrafikProdi' => $dataGrafikProdi,
                'periodeAktif' => $periodeAktif,
                'logPengguna' => $logPengguna,
                'persenKrs' => $persenKrs,
                'matkulBelumDiplot' => $matkulBelumDiplot,
                'jadwalHariIni' => $jadwalHariIni, // Variabel panel live
                'namaHariIni' => $hariIniTxt,
            ]);
        }
        // =========================================================
        // Dashboard Khusus DOSEN
        // =========================================================
        elseif ($user->hasRole('dosen') || $user->isKaprodi()) {
            $dosen = $user->dosen;
            if (!$dosen) {
                abort(404, 'Data dosen tidak ditemukan untuk akun ini.');
            }
            
            $mkAktifSaatIniIds = [];
            if ($periodeAktif) {
                $mkAktifSaatIniIds = DB::table('mahasiswa_mata_kuliah')
                                       ->where('tahun_akademik_id', $periodeAktif->id)
                                       ->distinct()
                                       ->pluck('mata_kuliah_id')
                                       ->toArray();
            }

            $pivotMkIds = DB::table('dosen_mata_kuliah')
                ->where('dosen_id', $dosen->id)
                ->pluck('mata_kuliah_id')
                ->toArray();
        
            $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
            
            $jadwalKuliahDosen = collect();
            if (!empty($mkAktifSaatIniIds)) {
                $jadwalKuliahDosen = Jadwal::whereHas('mataKuliah', function ($query) use ($dosen, $pivotMkIds) {
                    $query->where('dosen_id', $dosen->id)
                          ->orWhereIn('id', $pivotMkIds);
                })
                ->whereIn('mata_kuliah_id', $mkAktifSaatIniIds)
                ->with('mataKuliah')
                ->get()
                ->sortBy(fn($jadwal) => $hariOrder[$jadwal->hari] ?? 99);
            }
            
            $mataKuliahDosen = MataKuliah::where(function($query) use ($dosen, $pivotMkIds) {
                    $query->where('dosen_id', $dosen->id)
                          ->orWhereIn('id', $pivotMkIds);
                })
                ->whereIn('id', $mkAktifSaatIniIds)
                ->withCount(['mahasiswas' => function ($query) use ($periodeAktif) {
                    $query->where('mahasiswas.status_krs', 'Disetujui')
                          ->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeAktif->id ?? null);
                }])
                ->get();
            
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

            $querySurat = $dosen->suratKeputusans()->where('status', 'Selesai');
            
            if (request()->filled('search_surat')) {
                $search = request('search_surat');
                $querySurat->where(function($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhere('nomor_surat', 'like', "%{$search}%");
                });
            }

            $suratKeputusans = $querySurat->latest()->paginate(5)->withQueryString();

            return view('dosen.dashboard', [
                'dosen' => $dosen,
                'mata_kuliahs' => $mataKuliahDosen, 
                'jumlahMahasiswaWali' => $dosen->mahasiswaWali()->count(),
                'dataKaprodi' => $dataKaprodi,
                'pengumumans' => $pengumumans,
                'jadwalKuliah' => $jadwalKuliahDosen, 
                'suratKeputusans' => $suratKeputusans,
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
                'peminjamanAktif' => Peminjaman::where('status', 'Dipinjam')->count(),
                'terlambat' => Peminjaman::where('status', 'Dipinjam')->where('jatuh_tempo', '<', now())->count(),
                'aktivitasTerakhir' => Peminjaman::with(['user', 'koleksi'])->latest()->take(5)->get(),
                'pengumumans' => $pengumumans
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
                'suratSelesai' => $suratSelesai,
                'suratMenunggu' => $suratMenunggu,
                'suratDraf' => $suratDraf,
                'totalDokumen' => $totalDokumen,
                'suratTerbaru' => $suratTerbaru,
                'dokumenTerbaru' => $dokumenTerbaru,
                'pengumumans' => $pengumumans
            ]);
        }
        // =========================================================
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