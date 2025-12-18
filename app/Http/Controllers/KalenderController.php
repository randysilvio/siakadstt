<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KegiatanAkademik;
use App\Models\Role;
// Pastikan Anda mengimport Model Jadwal Kuliah Anda (sesuaikan nama modelnya)
use App\Models\JadwalKuliah; 
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KalenderController extends Controller
{
    /**
     * Menampilkan halaman manajemen kalender untuk admin.
     */
    public function index(): View
    {
        $kegiatans = KegiatanAkademik::with('roles')->latest()->paginate(10);
        return view('kalender.index', compact('kegiatans'));
    }

    /**
     * Menampilkan formulir untuk membuat kegiatan baru.
     */
    public function create(): View
    {
        $roles = Role::where('name', '!=', 'admin')->orderBy('name')->get();
        return view('kalender.create', compact('roles'));
    }

    /**
     * Menyimpan kegiatan baru ke database.
     */
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

        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir untuk mengedit kegiatan.
     */
    public function edit(KegiatanAkademik $kalender): View
    {
        $roles = Role::where('name', '!=', 'admin')->orderBy('name')->get();
        $kalender->load('roles');
        return view('kalender.edit', compact('kalender', 'roles'));
    }

    /**
     * Memperbarui kegiatan di database.
     */
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

    /**
     * Menghapus kegiatan dari database.
     */
    public function destroy(KegiatanAkademik $kalender): RedirectResponse
    {
        $kalender->delete();
        return redirect()->route('admin.kalender.index')->with('success', 'Kegiatan akademik berhasil dihapus.');
    }

    /**
     * Menampilkan halaman kalender publik (FullCalendar Web).
     */
    public function halamanKalender(): View
    {
        return view('kalender.show');
    }

    /**
     * Menyediakan data event untuk FullCalendar (Web).
     */
    public function getEvents(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
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
            // FullCalendar exclusive end date fix
            $kegiatan->end = Carbon::parse($kegiatan->end)->addDay()->toDateString();
            return $kegiatan;
        });

        return response()->json($events);
    }
    
    /**
     * [API MOBILE] Data Kalender Akademik.
     * Mengambil semua kegiatan (tanpa limit) agar kalender penuh terisi.
     */
    public function getKalenderUntukApi(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user || !$user->roles) {
            return response()->json([]);
        }
        $userRoleIds = $user->roles->pluck('id');

        // Mengambil data mulai dari 1 bulan yang lalu agar user bisa lihat history bulan berjalan
        $kegiatans = KegiatanAkademik::query()
            ->where('tanggal_selesai', '>=', Carbon::today()->startOfMonth()->subMonths(1))
            ->whereHas('roles', function ($q) use ($userRoleIds) {
                $q->whereIn('roles.id', $userRoleIds);
            })
            ->orderBy('tanggal_mulai', 'asc')
            // ->limit(5)  <-- DIHAPUS agar kalender di HP muncul semua
            ->get();
            
        return response()->json($kegiatans);
    }

    /**
     * [API MOBILE] Jadwal Kuliah Hari Ini.
     * Digunakan di DashboardScreen.tsx
     */
    public function jadwalHariIni(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Setup Hari dalam Bahasa Indonesia
        Carbon::setLocale('id');
        $hariIni = Carbon::now()->isoFormat('dddd'); // Senin, Selasa, dst.

        $jadwal = [];

        if ($user->hasRole('mahasiswa')) {
            // Logika Mahasiswa: Ambil dari KRS yang diambil user
            // Asumsi: Ada relasi 'mataKuliahs' di model Mahasiswa, dan tabel pivot/matkul punya data 'hari', 'jam_mulai'
            
            $mahasiswa = $user->mahasiswa; // Pastikan relasi user->mahasiswa ada
            if($mahasiswa) {
                 // Query contoh (sesuaikan dengan struktur tabel Jadwal/KRS Anda)
                 // Ini mengambil mata kuliah yang diambil mahasiswa, lalu memfilter hari
                 $jadwal = \App\Models\JadwalKuliah::query()
                    ->where('hari', $hariIni)
                    ->whereHas('mataKuliah.mahasiswas', function($q) use ($mahasiswa) {
                        $q->where('mahasiswas.id', $mahasiswa->id);
                    })
                    ->with(['mataKuliah', 'ruangan', 'dosen'])
                    ->orderBy('jam_mulai')
                    ->get()
                    ->map(function($item) {
                        return [
                            'id' => $item->id,
                            'mata_kuliah' => $item->mataKuliah->nama_mk,
                            'kode_mk' => $item->mataKuliah->kode_mk,
                            'jam_mulai' => $item->jam_mulai,
                            'jam_selesai' => $item->jam_selesai,
                            'ruang' => $item->ruangan->nama_ruang ?? 'Online/TBA',
                            'dosen' => $item->dosen->nama_lengkap ?? '-'
                        ];
                    });
            }

        } elseif ($user->hasRole('dosen')) {
            // Logika Dosen: Ambil jadwal mengajar dia
            $dosen = $user->dosen;
            if($dosen) {
                $jadwal = \App\Models\JadwalKuliah::query()
                    ->where('hari', $hariIni)
                    ->where('dosen_id', $dosen->id)
                    ->with(['mataKuliah', 'ruangan'])
                    ->orderBy('jam_mulai')
                    ->get()
                    ->map(function($item) {
                        return [
                            'id' => $item->id,
                            'mata_kuliah' => $item->mataKuliah->nama_mk,
                            'kode_mk' => $item->mataKuliah->kode_mk,
                            'jam_mulai' => $item->jam_mulai,
                            'jam_selesai' => $item->jam_selesai,
                            'ruang' => $item->ruangan->nama_ruang ?? '-',
                            'dosen' => 'Anda Sendiri'
                        ];
                    });
            }
        }

        return response()->json($jadwal);
    }
}