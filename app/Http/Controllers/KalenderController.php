<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KegiatanAkademik;
use App\Models\Role;
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
     * Menampilkan halaman kalender publik.
     */
    public function halamanKalender(): View
    {
        return view('kalender.show');
    }

    /**
     * Menyediakan data event untuk FullCalendar.
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
            $kegiatan->end = Carbon::parse($kegiatan->end)->addDay()->toDateString();
            return $kegiatan;
        });

        return response()->json($events);
    }
    
    /**
     * Menyediakan data kalender untuk API aplikasi seluler.
     */
    public function getKalenderUntukApi(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user || !$user->roles) {
            return response()->json([]);
        }
        $userRoleIds = $user->roles->pluck('id');

        // Mengambil 5 kegiatan terdekat yang akan datang
        $kegiatans = KegiatanAkademik::query()
            ->where('tanggal_selesai', '>=', Carbon::today())
            ->whereHas('roles', function ($q) use ($userRoleIds) {
                $q->whereIn('roles.id', $userRoleIds);
            })
            ->orderBy('tanggal_mulai', 'asc')
            ->limit(5)
            ->get();
            
        return response()->json($kegiatans);
    }
}