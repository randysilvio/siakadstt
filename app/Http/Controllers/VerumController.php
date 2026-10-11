<?php

namespace App\Http\Controllers;

use App\Models\TahunAkademik;
use App\Models\VerumKelas;
use App\Models\VerumPostingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VerumController extends Controller
{
    public function __construct()
    {
        // [UPDATE] Tambahkan destroy pada middleware dosen
        $this->middleware('role:dosen')->only(['create', 'store', 'startMeeting', 'stopMeeting', 'destroy']);
    }

    public function index(): View
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin') && !$user->hasRole('dosen') && !$user->hasRole('mahasiswa')) {
            abort(403, 'Akses ditolak. Administrator tidak memiliki akses ke E-Learning.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();
        $semuaKelas = collect();

        if (!$tahunAkademikAktif) {
            return view('verum.index', ['semuaKelas' => $semuaKelas])
                   ->with('error', 'Saat ini tidak ada Tahun Akademik yang aktif.');
        }

        if ($user->hasRole('dosen') && $user->dosen) {
            $semuaKelas = VerumKelas::where('dosen_id', $user->dosen->id)
                                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->with('mataKuliah')
                                ->get();
        } elseif ($user->hasRole('mahasiswa') && $user->mahasiswa) {
            $krsMataKuliahIds = $user->mahasiswa->mataKuliahs()->pluck('mata_kuliahs.id');

            $semuaKelas = VerumKelas::whereIn('mata_kuliah_id', $krsMataKuliahIds)
                                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->with(['mataKuliah', 'dosen.user'])
                                ->get();
        } else {
            abort(403, 'Data profil akademik tidak ditemukan.');
        }

        return view('verum.index', compact('semuaKelas'));
    }

    public function show(VerumKelas $verum_kela): View
    {
        $user = Auth::user();

        if ($user->hasRole('admin') && !$user->hasRole('dosen')) {
            abort(403, 'Akses ditolak.');
        }

        $isDosenPemilik = $user->dosen && $user->dosen->id == $verum_kela->dosen_id;
        $isMahasiswaTerdaftar = false;

        if ($user->mahasiswa) {
            $isMahasiswaTerdaftar = $user->mahasiswa->mataKuliahs->contains($verum_kela->mata_kuliah_id);
        }

        if (!$isDosenPemilik && !$isMahasiswaTerdaftar) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }

        $verum_kela->load(['materi', 'tugas', 'postingan.user', 'presensi.kehadiran']);
        return view('verum.show', compact('verum_kela'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (!$user->dosen) {
             abort(404, 'Data dosen tidak ditemukan.');
        }

        $mataKuliahs = $user->dosen->mataKuliahs;
        return view('verum.create', compact('mataKuliahs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'nama_kelas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string'
        ]);

        $user = Auth::user();
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();

        if (!$tahunAkademikAktif) {
            return back()->with('error', 'Tidak ada tahun akademik aktif.');
        }

        if (!$user->dosen->mataKuliahs->contains($request->mata_kuliah_id)) {
            return back()->with('error', 'Anda tidak mengampu mata kuliah ini.');
        }

        VerumKelas::create([
            'mata_kuliah_id' => $request->mata_kuliah_id,
            'nama_kelas' => $request->nama_kelas,
            'deskripsi' => $request->deskripsi,
            'dosen_id' => $user->dosen->id,
            'tahun_akademik_id' => $tahunAkademikAktif->id,
            'kode_kelas' => Str::upper(Str::random(6)),
        ]);

        return redirect()->route('verum.index')->with('success', 'Kelas Verum berhasil dibuat.');
    }

    // [BARU] Hapus Kelas
    public function destroy(VerumKelas $verum_kela): RedirectResponse
    {
        if (!Auth::user()->dosen || Auth::user()->dosen->id !== $verum_kela->dosen_id) {
            abort(403, 'Anda tidak berhak menghapus kelas ini.');
        }

        $verum_kela->delete();
        return redirect()->route('verum.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function storePost(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        $request->validate(['konten' => 'required|string']);
        VerumPostingan::create([
            'kelas_id' => $verum_kela->id,
            'user_id' => Auth::id(),
            'konten' => $request->konten
        ]);

        return back()->with('success', 'Postingan berhasil ditambahkan.');
    }

    // [BARU] Hapus Postingan
    public function destroyPost(VerumPostingan $postingan): RedirectResponse
    {
        $isPemilikPost = $postingan->user_id === Auth::id();
        $isDosenKelas = Auth::user()->dosen && Auth::user()->dosen->id === $postingan->kelas->dosen_id;

        if (!$isPemilikPost && !$isDosenKelas) {
            abort(403, 'Anda tidak berhak menghapus postingan ini.');
        }

        $postingan->delete();
        return back()->with('success', 'Postingan berhasil dihapus.');
    }

    public function startMeeting(VerumKelas $verum_kela): RedirectResponse
    {
        if (Auth::user()->dosen && Auth::user()->dosen->id == $verum_kela->dosen_id) {
            $verum_kela->update(['is_meeting_active' => true]);
            return back()->with('success', 'Ruang kelas online telah dibuka.');
        }
        return back()->with('error', 'Anda tidak memiliki akses.');
    }

    public function stopMeeting(VerumKelas $verum_kela): RedirectResponse
    {
        if (Auth::user()->dosen && Auth::user()->dosen->id == $verum_kela->dosen_id) {
            $verum_kela->update(['is_meeting_active' => false]);
            return back()->with('success', 'Kelas online telah diakhiri.');
        }
        return back()->with('error', 'Anda tidak memiliki akses.');
    }
}