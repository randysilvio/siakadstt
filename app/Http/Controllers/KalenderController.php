<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KegiatanAkademik;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KalenderController extends Controller
{
    // Bagian CRUD Admin (tidak ada perubahan)...
    public function index()
    {
        $kegiatans = KegiatanAkademik::latest()->paginate(10);
        return view('kalender.index', compact('kegiatans'));
    }
    public function create()
    {
        return view('kalender.create');
    }
    public function store(Request $request)
    {
        $request->validate(['judul_kegiatan' => 'required|string|max:255', 'deskripsi' => 'nullable|string', 'tanggal_mulai' => 'required|date', 'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai', 'target_role' => 'required|in:semua,mahasiswa,dosen',]);
        KegiatanAkademik::create($request->all());
        return redirect()->route('kalender.index')->with('success', 'Kegiatan akademik berhasil ditambahkan.');
    }
    public function edit(KegiatanAkademik $kalender)
    {
        return view('kalender.edit', compact('kalender'));
    }
    public function update(Request $request, KegiatanAkademik $kalender)
    {
        $request->validate(['judul_kegiatan' => 'required|string|max:255', 'deskripsi' => 'nullable|string', 'tanggal_mulai' => 'required|date', 'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai', 'target_role' => 'required|in:semua,mahasiswa,dosen',]);
        $kalender->update($request->all());
        return redirect()->route('kalender.index')->with('success', 'Kegiatan akademik berhasil diperbarui.');
    }
    public function destroy(KegiatanAkademik $kalender)
    {
        $kalender->delete();
        return redirect()->route('kalender.index')->with('success', 'Kegiatan akademik berhasil dihapus.');
    }

    // Bagian Tampilan Kalender...
    public function halamanKalender()
    {
        return view('kalender.show');
    }

    public function getEvents(Request $request)
    {
        $userRole = Auth::user()->role;

        $query = KegiatanAkademik::query()
            ->where('tanggal_mulai', '<=', $request->end)
            ->where('tanggal_selesai', '>=', $request->start);

        $query->where(function($q) use ($userRole) {
            $q->where('target_role', 'semua')
              ->orWhere('target_role', $userRole);
        });
        
        // --- PERUBAHAN DI SINI: Tambahkan 'target_role' ke dalam get() ---
        $kegiatans = $query->get(['id', 'judul_kegiatan as title', 'tanggal_mulai as start', 'tanggal_selesai as end', 'deskripsi', 'target_role']);

        $events = $kegiatans->map(function ($kegiatan) {
            $kegiatan->end = Carbon::parse($kegiatan->end)->addDay()->toDateString();
            return $kegiatan;
        });

        return response()->json($events);
    }
}