<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KegiatanAkademik;
use App\Models\Role;
use App\Models\Jadwal; 
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Services\KalenderPengendali;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KalenderController extends Controller
{
    public function index(Request $request): View
    {
        $kegiatans = KegiatanAkademik::with(['roles', 'tahunAkademik', 'pmbPeriod'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w->where('judul_kegiatan', 'like', '%' . $request->input('search') . '%')
                ->orWhere('deskripsi', 'like', '%' . $request->input('search') . '%')))
            ->orderByDesc('tanggal_mulai')
            ->paginate(10);
        return view('kalender.index', compact('kegiatans'));
    }

    private function pilihanKendali(): array
    {
        return [
            'tahunAkademiks' => TahunAkademik::orderByDesc('tahun')->orderByDesc('semester')->get(),
            'pmbPeriods'     => \App\Models\PmbPeriod::orderByDesc('tahun_pmb')->orderBy('nama_gelombang')->get(),
        ];
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        return view('kalender.create', array_merge(compact('roles'), $this->pilihanKendali()));
    }

    public function store(Request $request): RedirectResponse
    {
        $kegiatan = KalenderPengendali::simpan($request->validate(KalenderPengendali::ATURAN));

        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil ditambahkan.'
            . (KalenderPengendali::pengendali($kegiatan) ? ' Pengaturan sistem terkait sudah diperbarui otomatis.' : ''));
    }

    public function edit(KegiatanAkademik $kalender): View
    {
        $roles = Role::orderBy('name')->get();
        $kalender->load('roles');
        return view('kalender.edit', array_merge(compact('kalender', 'roles'), $this->pilihanKendali()));
    }

    public function update(Request $request, KegiatanAkademik $kalender): RedirectResponse
    {
        KalenderPengendali::simpan($request->validate(KalenderPengendali::ATURAN), $kalender);

        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil diperbarui.');
    }

    public function destroy(KegiatanAkademik $kalender): RedirectResponse
    {
        KalenderPengendali::hapus($kalender);
        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil dihapus.'
            . (KalenderPengendali::pengendali($kalender) ? ' Tanggal terakhir tetap tersimpan di pengaturan terkait dan kini bisa diubah manual.' : ''));
    }

    private function tanggalAtau($nilai, Carbon $bawaan): string
    {
        try {
            return $nilai ? Carbon::parse($nilai)->toDateString() : $bawaan->toDateString();
        } catch (\Throwable $e) {
            return $bawaan->toDateString();
        }
    }

    public function halamanKalender(): View
    {
        return view('kalender.show');
    }

    public function getEvents(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([]);
        }
        $userRoleIds = $user->idPeranEfektif();

        // FullCalendar selalu mengirim start & end; bila dibuka langsung, tampilkan bulan berjalan
        $mulai = $this->tanggalAtau($request->query('start'), now()->startOfMonth());
        $akhir = $this->tanggalAtau($request->query('end'), now()->endOfMonth());

        $query = KegiatanAkademik::query()
            ->whereDate('tanggal_mulai', '<=', $akhir)
            ->whereDate('tanggal_selesai', '>=', $mulai)
            // Admin pengelola kalender melihat seluruh agenda
            ->when(!$user->hasRole('admin'), fn ($q) => $q->whereHas('roles', function ($r) use ($userRoleIds) {
                $r->whereIn('roles.id', $userRoleIds);
            }));
        
        $kegiatans = $query->get(['id', 'judul_kegiatan as title', 'tanggal_mulai as start', 'tanggal_selesai as end', 'deskripsi']);

        $events = $kegiatans->map(function ($kegiatan) {
            $kegiatan->end = Carbon::parse($kegiatan->end)->addDay()->toDateString();
            return $kegiatan;
        });

        return response()->json($events);
    }
    
    public function getKalenderUntukApi(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([]);
        }
        $userRoleIds = $user->idPeranEfektif();

        $kegiatans = KegiatanAkademik::query()
            ->where('tanggal_selesai', '>=', Carbon::today()->startOfMonth()->subMonths(1))
            ->whereHas('roles', function ($q) use ($userRoleIds) {
                $q->whereIn('roles.id', $userRoleIds);
            })
            ->orderBy('tanggal_mulai', 'asc')
            ->get();
            
        return response()->json($kegiatans);
    }

    public function jadwalHariIni(Request $request): JsonResponse
    {
        $mapHari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hariIni = $mapHari[date('l')] ?? 'Senin';

        $semuaJadwal = $this->jadwalKuliahUser($request)->getData(true);
        if (!is_array($semuaJadwal)) {
            return response()->json([]);
        }

        $jadwalHariIni = array_values(array_filter($semuaJadwal, function ($item) use ($hariIni) {
            return isset($item['hari']) && strcasecmp($item['hari'], $hariIni) === 0;
        }));

        return response()->json($jadwalHariIni);
    }

    public function jadwalKuliahUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $jadwal = [];

            if ($user->hasRole('mahasiswa')) {
                $mahasiswa = $user->mahasiswa;
                
                $periodeAktif = TahunAkademik::where('is_active', true)->first();
                if ($mahasiswa && $periodeAktif && $mahasiswa->krsDisetujuiPada($periodeAktif->id)) {
                        $jadwal = app(\App\Services\JadwalService::class)->jadwalMahasiswa($mahasiswa, $periodeAktif->id)
                            ->map(function ($item) {
                                return [
                                    'id'          => $item->id,
                                    'hari'        => $item->hari,
                                    'nama_kelas'  => $item->nama_kelas ?? 'A',
                                    'mata_kuliah' => $item->mataKuliah->nama_mk ?? 'Mata Kuliah',
                                    'kode_mk'     => $item->mataKuliah->kode_mk ?? '-',
                                    'jam_mulai'   => $item->jam_mulai,
                                    'jam_selesai' => $item->jam_selesai,
                                    'ruang'       => $item->ruang ?? 'TBA', 
                                    'dosen'       => $item->mataKuliah->dosen->nama_lengkap ?? '-'
                                ];
                            });
                }
            } elseif ($user->hasRole('dosen')) {
                $dosen = $user->dosen;
                if ($dosen) {
                    $pivotMkIds = DB::table('dosen_mata_kuliah')
                        ->where('dosen_id', $dosen->id)
                        ->pluck('mata_kuliah_id')
                        ->toArray();

                    $mkIds = MataKuliah::where('dosen_id', $dosen->id)
                        ->orWhereIn('id', $pivotMkIds)
                        ->pluck('id')
                        ->toArray();
                    
                    $jadwal = app(\App\Services\JadwalService::class)->urutkan(
                            Jadwal::whereIn('mata_kuliah_id', $mkIds)->with('mataKuliah')->get()
                        )
                        ->map(function ($item) {
                            return [
                                'id'          => $item->id,
                                'hari'        => $item->hari,
                                'nama_kelas'  => $item->nama_kelas ?? 'A',
                                'mata_kuliah' => $item->mataKuliah->nama_mk ?? 'Mata Kuliah',
                                'kode_mk'     => $item->mataKuliah->kode_mk ?? '-',
                                'jam_mulai'   => $item->jam_mulai,
                                'jam_selesai' => $item->jam_selesai,
                                'ruang'       => $item->ruang ?? 'TBA',
                                'dosen'       => 'Anda Sendiri'
                            ];
                        });
                }
            }

            return response()->json($jadwal);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memuat jadwal kuliah. Silakan coba lagi.'
            ], 500);
        }
    }
}