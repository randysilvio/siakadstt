<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

use App\Models\TahunAkademik;
use App\Models\VerumKelas;
use App\Models\VerumPostingan;
use App\Models\MataKuliah;
use App\Services\VerumService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VerumController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:dosen')->only(['create', 'store', 'startMeeting', 'stopMeeting', 'destroy']);
    }
    /**
     * Helper untuk mengecek apakah mahasiswa resmi terdaftar di kelas pada tahun akademik kelas tersebut.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin') && !$user->hasRole('dosen') && !$user->hasRole('mahasiswa')) {
            abort(403, 'Akses ditolak. Administrator tidak memiliki akses ke E-Learning.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();

        if (!$tahunAkademikAktif) {
            return view('verum.index', ['semuaKelas' => collect()])
                   ->with('error', 'Saat ini tidak ada Tahun Akademik yang aktif.');
        }

        if (!($user->hasRole('dosen') && $user->dosen) && !($user->hasRole('mahasiswa') && $user->mahasiswa)) {
            abort(403, 'Data profil akademik tidak ditemukan.');
        }

        $semuaKelas = app(VerumService::class)->kelasUntuk($user, $tahunAkademikAktif);

        return view('verum.index', compact('semuaKelas'));
    }

    public function show(VerumKelas $verum_kela): View
    {
        $user = Auth::user();

        if ($user->hasRole('admin') && !$user->hasRole('dosen')) {
            abort(403, 'Akses ditolak.');
        }

        $isDosenPemilik = Gate::forUser($user)->allows('kelola', $verum_kela);
        $isMahasiswaTerdaftar = Gate::forUser($user)->allows('ikuti', $verum_kela);

        if (!$isDosenPemilik && !$isMahasiswaTerdaftar) {
            abort(403, 'Anda tidak terdaftar di kelas ini atau KRS Anda belum disetujui.');
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

        $mataKuliahs = app(VerumService::class)->mataKuliahDiampu($user->dosen->id);

        return view('verum.create', compact('mataKuliahs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(VerumService::ATURAN_KELAS);

        $hasil = app(VerumService::class)->buatKelas(Auth::user(), $data);
        if (is_string($hasil)) {
            return back()->withInput()->with('error', $hasil);
        }

        return redirect()->route('verum.index')->with('success', 'Kelas Verum berhasil dibuat.');
    }

    public function destroy(VerumKelas $verum_kela): RedirectResponse
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            abort(403, 'Anda tidak berhak menghapus kelas ini.');
        }

        $verum_kela->delete();
        return redirect()->route('verum.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function storePost(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        $user = Auth::user();

        // [PERBAIKAN KEAMANAN FASE 5]: Cegah IDOR! Hanya dosen kelas atau mahasiswa peserta kelas yang boleh posting di forum
        if (!Gate::forUser($user)->allows('kelola', $verum_kela) && !Gate::forUser($user)->allows('ikuti', $verum_kela)) {
            abort(403, 'Akses ditolak. Anda bukan anggota kelas ini.');
        }

        $request->validate(['konten' => 'required|string|max:2000']);

        VerumPostingan::create([
            'kelas_id' => $verum_kela->id,
            'user_id' => $user->id,
            'konten' => strip_tags($request->konten)
        ]);

        return back()->with('success', 'Postingan berhasil ditambahkan.');
    }

    public function destroyPost(VerumPostingan $postingan): RedirectResponse
    {
        $user = Auth::user();
        $isPemilikPost = ((int) $postingan->user_id === (int) $user->id);
        $isDosenKelas = $postingan->kelas && Gate::forUser($user)->allows('kelola', $postingan->kelas);

        if (!$isPemilikPost && !$isDosenKelas) {
            abort(403, 'Anda tidak berhak menghapus postingan ini.');
        }

        $postingan->delete();
        return back()->with('success', 'Postingan berhasil dihapus.');
    }

    public function startMeeting(VerumKelas $verum_kela): RedirectResponse
    {
        if (Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            $verum_kela->update(['is_meeting_active' => true]);
            return back()->with('success', 'Ruang kelas online telah dibuka.');
        }
        return back()->with('error', 'Anda tidak memiliki akses.');
    }

    public function stopMeeting(VerumKelas $verum_kela): RedirectResponse
    {
        if (Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            $verum_kela->update(['is_meeting_active' => false]);
            return back()->with('success', 'Kelas online telah diakhiri.');
        }
        return back()->with('error', 'Anda tidak memiliki akses.');
    }
}