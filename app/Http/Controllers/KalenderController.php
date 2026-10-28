<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KegiatanAkademik;
use App\Models\Role;
use App\Models\User; 
use App\Models\Jadwal; 
use App\Models\MataKuliah;
use App\Models\TahunAkademik; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification; 
use App\Notifications\GeneralNotification; 
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KalenderController extends Controller
{
    public function index(): View
    {
        $kegiatans = KegiatanAkademik::with('roles')->latest()->paginate(10);
        return view('kalender.index', compact('kegiatans'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        return view('kalender.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul_kegiatan'  => 'required|string|max:255',
            'deskripsi'       => 'nullable|string',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'target_roles'    => 'required|array',
            'target_roles.*'  => 'exists:roles,id',
        ]);

        $kegiatan = KegiatanAkademik::create([
            'judul_kegiatan'  => $validated['judul_kegiatan'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
        ]);
        $kegiatan->roles()->sync($validated['target_roles']);

        $usersToNotify = User::pemegangPeran(array_map('intval', $validated['target_roles']));

        if ($usersToNotify->count() > 0) {
            Notification::send($usersToNotify, new GeneralNotification(
                'Agenda Akademik Baru',
                $kegiatan->judul_kegiatan . ' dijadwalkan pada ' . Carbon::parse($kegiatan->tanggal_mulai)->translatedFormat('d F Y'),
                route('kalender.halaman'),
                'bi-calendar-event-fill text-dark'
            ));
        }

        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil ditambahkan.');
    }

    public function edit(KegiatanAkademik $kalender): View
    {
        $roles = Role::orderBy('name')->get();
        $kalender->load('roles');
        return view('kalender.edit', compact('kalender', 'roles'));
    }

    public function update(Request $request, KegiatanAkademik $kalender): RedirectResponse
    {
        $validated = $request->validate([
            'judul_kegiatan'  => 'required|string|max:255',
            'deskripsi'       => 'nullable|string',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'target_roles'    => 'required|array',
            'target_roles.*'  => 'exists:roles,id',
        ]);

        $kalender->update([
            'judul_kegiatan'  => $validated['judul_kegiatan'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
        ]);
        $kalender->roles()->sync($validated['target_roles']);

        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil diperbarui.');
    }

    public function destroy(KegiatanAkademik $kalender): RedirectResponse
    {
        $kalender->delete();
        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil dihapus.');
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