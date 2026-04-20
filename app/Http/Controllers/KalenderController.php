<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KegiatanAkademik;
use App\Models\Role;
use App\Models\User; 
// PERBAIKAN FATAL: Menggunakan model Jadwal, bukan JadwalKuliah
use App\Models\Jadwal; 
use App\Models\TahunAkademik; 
use Illuminate\Support\Facades\Auth;
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
        $roles = Role::where('name', '!=', 'admin')->orderBy('name')->get();
        return view('kalender.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'target_roles' => 'required|array',
            'target_roles.*' => 'exists:roles,id',
        ]);

        $kegiatan = KegiatanAkademik::create($request->except('target_roles'));
        $kegiatan->roles()->sync($request->target_roles);

        $targetRoleIds = $request->target_roles;
        $usersToNotify = User::whereHas('roles', function($q) use ($targetRoleIds) {
            $q->whereIn('roles.id', $targetRoleIds);
        })->get();

        if ($usersToNotify->count() > 0) {
            Notification::send($usersToNotify, new GeneralNotification(
                'Agenda Akademik Baru',
                $kegiatan->judul_kegiatan . ' dijadwalkan pada ' . Carbon::parse($kegiatan->tanggal_mulai)->translatedFormat('d F Y'),
                route('kalender.halaman'),
                'bi-calendar-event-fill text-warning'
            ));
        }

        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil ditambahkan.');
    }

    public function edit(KegiatanAkademik $kalender): View
    {
        $roles = Role::where('name', '!=', 'admin')->orderBy('name')->get();
        $kalender->load('roles');
        return view('kalender.edit', compact('kalender', 'roles'));
    }

    public function update(Request $request, KegiatanAkademik $kalender): RedirectResponse
    {
        $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'target_roles' => 'required|array',
            'target_roles.*' => 'exists:roles,id',
        ]);

        $kalender->update($request->except('target_roles'));
        $kalender->roles()->sync($request->target_roles);

        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil diperbarui.');
    }

    public function destroy(KegiatanAkademik $kalender): RedirectResponse
    {
        $kalender->delete();
        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil dihapus.');
    }

    public function halamanKalender(): View
    {
        return view('kalender.show');
    }

    public function getEvents(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->roles) {
            return response()->json([]);
        }
        $userRoleIds = $user->roles->pluck('id');

        $query = KegiatanAkademik::query()
            ->where('tanggal_mulai', '<=', $request->end)
            ->where('tanggal_selesai', '>=', $request->start)
            ->whereHas('roles', function ($q) use ($userRoleIds) {
                $q->whereIn('roles.id', $userRoleIds);
            });
        
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

        if (!$user || !$user->roles) {
            return response()->json([]);
        }
        $userRoleIds = $user->roles->pluck('id');

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
        return response()->json([]); 
    }

    /**
     * [API MOBILE] Jadwal Kuliah Seluruhnya (Semester Ini).
     */
    public function jadwalKuliahUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $jadwal = [];

            if ($user->hasRole('mahasiswa')) {
                $mahasiswa = $user->mahasiswa;
                
                // Pastikan KRS Disetujui
                if ($mahasiswa && $mahasiswa->status_krs === 'Disetujui') {
                    $periodeAktif = TahunAkademik::where('is_active', true)->first();
                    
                    if ($periodeAktif) {
                        // Ambil ID Mata Kuliah semester ini
                        $mkIds = $mahasiswa->mataKuliahs()
                            ->wherePivot('tahun_akademik_id', $periodeAktif->id)
                            ->pluck('mata_kuliahs.id')
                            ->toArray();

                        // PERBAIKAN: Gunakan model Jadwal dan relasi yang sesuai dengan show_jadwal.blade.php
                        $jadwal = Jadwal::query()
                            ->whereIn('mata_kuliah_id', $mkIds)
                            ->with(['mataKuliah.dosen']) // Dosen nempel di mata kuliah
                            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                            ->orderBy('jam_mulai')
                            ->get()
                            ->map(function($item) {
                                return [
                                    'id' => $item->id,
                                    'hari' => $item->hari,
                                    'mata_kuliah' => $item->mataKuliah->nama_mk ?? 'Mata Kuliah',
                                    'kode_mk' => $item->mataKuliah->kode_mk ?? '-',
                                    'jam_mulai' => $item->jam_mulai,
                                    'jam_selesai' => $item->jam_selesai,
                                    'ruang' => $item->ruang ?? 'TBA', 
                                    'dosen' => $item->mataKuliah->dosen->nama_lengkap ?? '-'
                                ];
                            });
                    }
                }
            } elseif ($user->hasRole('dosen')) {
                $dosen = $user->dosen;
                if($dosen) {
                    // Cari MK yang diajar dosen ini
                    $mkIds = \App\Models\MataKuliah::where('dosen_id', $dosen->id)->pluck('id')->toArray();
                    
                    $jadwal = Jadwal::query()
                        ->whereIn('mata_kuliah_id', $mkIds)
                        ->with(['mataKuliah'])
                        ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                        ->orderBy('jam_mulai')
                        ->get()
                        ->map(function($item) {
                            return [
                                'id' => $item->id,
                                'hari' => $item->hari,
                                'mata_kuliah' => $item->mataKuliah->nama_mk ?? 'Mata Kuliah',
                                'kode_mk' => $item->mataKuliah->kode_mk ?? '-',
                                'jam_mulai' => $item->jam_mulai,
                                'jam_selesai' => $item->jam_selesai,
                                'ruang' => $item->ruang ?? 'TBA',
                                'dosen' => 'Anda Sendiri'
                            ];
                        });
                }
            }

            return response()->json($jadwal);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error Backend: ' . $e->getMessage()
            ], 500);
        }
    }
}