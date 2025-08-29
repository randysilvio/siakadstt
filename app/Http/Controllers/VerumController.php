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
    /**
     * Terapkan middleware otorisasi pada method tertentu.
     */
    public function __construct()
    {
        // Middleware ini akan memastikan hanya user dengan role 'dosen'
        // yang bisa mengakses method create dan store.
        $this->middleware('role:dosen')->only(['create', 'store']);
    }

    /**
     * Menampilkan daftar kelas Verum yang bisa diakses oleh pengguna.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();
        $semuaKelas = collect();

        if (!$tahunAkademikAktif) {
            return view('verum.index', ['semuaKelas' => $semuaKelas])
                   ->with('error', 'Saat ini tidak ada Tahun Akademik yang aktif.');
        }

        // Logika baru menggunakan sistem multi-peran
        if ($user->hasRole('dosen') && $user->dosen) {
            $semuaKelas = VerumKelas::where('dosen_id', $user->dosen->id)
                                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->with('mataKuliah') // Eager load relasi yang dibutuhkan di view
                                ->get();
        } elseif ($user->hasRole('mahasiswa') && $user->mahasiswa) {
            $krsMataKuliahIds = $user->mahasiswa->mataKuliahs()->pluck('mata_kuliahs.id');

            $semuaKelas = VerumKelas::whereIn('mata_kuliah_id', $krsMataKuliahIds)
                                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->with(['mataKuliah', 'dosen.user']) // Eager load relasi
                                ->get();
        }

        return view('verum.index', compact('semuaKelas'));
    }

    /**
     * Menampilkan detail satu kelas Verum.
     */
    public function show(VerumKelas $verum_kela): View
    {
        // Lakukan otorisasi di sini jika diperlukan
        // Contoh: $this->authorize('view', $verum_kela);

        $verum_kela->load(['materi', 'tugas', 'postingan.user', 'presensi.kehadiran']);
        return view('verum.show', compact('verum_kela'));
    }

    /**
     * Menampilkan form untuk membuat kelas baru.
     */
    public function create(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pengecekan data dosen tetap diperlukan untuk memastikan relasi ada
        if (!$user->dosen) {
             abort(404, 'Data dosen tidak ditemukan untuk pengguna ini.');
        }

        $mataKuliahs = $user->dosen->mataKuliahs;

        return view('verum.create', compact('mataKuliahs'));
    }

    /**
     * Menyimpan kelas baru yang dibuat.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'nama_kelas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();

        if (!$tahunAkademikAktif) {
            return back()->with('error', 'Tidak dapat membuat kelas karena tidak ada tahun akademik yang aktif.');
        }

        // Pengecekan data dosen tetap diperlukan
        if (!$user->dosen) {
            return back()->with('error', 'Data dosen tidak valid.');
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

    /**
     * Menyimpan postingan baru di forum kelas.
     */
    public function storePost(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        $request->validate(['konten' => 'required|string']);

        // Otorisasi: Pastikan user adalah anggota kelas (dosen atau mahasiswa)
        // Logika ini bisa ditambahkan jika diperlukan

        VerumPostingan::create([
            'kelas_id' => $verum_kela->id,
            'user_id' => Auth::id(),
            'konten' => $request->konten
        ]);

        return back()->with('success', 'Postingan berhasil ditambahkan.');
    }
}

