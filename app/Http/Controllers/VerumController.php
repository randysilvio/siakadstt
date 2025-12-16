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
        // HANYA DOSEN yang boleh membuat kelas atau mengatur meeting
        $this->middleware('role:dosen')->only(['create', 'store', 'startMeeting', 'stopMeeting']);
    }

    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // 1. BLOKIR ADMIN dan user tanpa peran akademik
        if ($user->hasRole('admin') && !$user->hasRole('dosen') && !$user->hasRole('mahasiswa')) {
            abort(403, 'Akses ditolak. Administrator tidak memiliki akses ke E-Learning.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();
        $semuaKelas = collect();

        if (!$tahunAkademikAktif) {
            return view('verum.index', ['semuaKelas' => $semuaKelas])
                   ->with('error', 'Saat ini tidak ada Tahun Akademik yang aktif.');
        }

        // 2. Logika Tampilan Berdasarkan Peran
        if ($user->hasRole('dosen') && $user->dosen) {
            // Dosen hanya melihat kelas miliknya
            $semuaKelas = VerumKelas::where('dosen_id', $user->dosen->id)
                                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->with('mataKuliah')
                                ->get();
        } elseif ($user->hasRole('mahasiswa') && $user->mahasiswa) {
            // Mahasiswa hanya melihat kelas yang diambil di KRS
            $krsMataKuliahIds = $user->mahasiswa->mataKuliahs()->pluck('mata_kuliahs.id');

            $semuaKelas = VerumKelas::whereIn('mata_kuliah_id', $krsMataKuliahIds)
                                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->with(['mataKuliah', 'dosen.user'])
                                ->get();
        } else {
            // Jika user tidak punya data dosen/mahasiswa valid
            abort(403, 'Data profil akademik tidak ditemukan.');
        }

        return view('verum.index', compact('semuaKelas'));
    }

    public function show(VerumKelas $verum_kela): View
    {
        $user = Auth::user();

        // BLOKIR ADMIN
        if ($user->hasRole('admin') && !$user->hasRole('dosen')) {
            abort(403, 'Akses ditolak.');
        }

        // Validasi Kepemilikan Akses
        $isDosenPemilik = $user->dosen && $user->dosen->id == $verum_kela->dosen_id;
        $isMahasiswaTerdaftar = false;

        if ($user->mahasiswa) {
            // Cek apakah mahasiswa mengambil matkul ini
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
        // Middleware sudah handle role:dosen, tapi pastikan data dosen ada
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

        // Validasi: Dosen hanya boleh buat kelas untuk matkul yang dia ampu
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

    public function storePost(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        $request->validate(['konten' => 'required|string']);
        // Akses view show() sudah divalidasi, jadi aman untuk insert
        VerumPostingan::create([
            'kelas_id' => $verum_kela->id,
            'user_id' => Auth::id(),
            'konten' => $request->konten
        ]);

        return back()->with('success', 'Postingan berhasil ditambahkan.');
    }

    public function startMeeting(VerumKelas $verum_kela): RedirectResponse
    {
        // Hanya Dosen Pemilik
        if (Auth::user()->dosen && Auth::user()->dosen->id == $verum_kela->dosen_id) {
            $verum_kela->update(['is_meeting_active' => true]);
            return back()->with('success', 'Ruang kelas online telah dibuka.');
        }
        return back()->with('error', 'Anda tidak memiliki akses.');
    }

    public function stopMeeting(VerumKelas $verum_kela): RedirectResponse
    {
        // Hanya Dosen Pemilik
        if (Auth::user()->dosen && Auth::user()->dosen->id == $verum_kela->dosen_id) {
            $verum_kela->update(['is_meeting_active' => false]);
            return back()->with('success', 'Kelas online telah diakhiri.');
        }
        return back()->with('error', 'Anda tidak memiliki akses.');
    }
}