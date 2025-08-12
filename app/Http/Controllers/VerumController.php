<?php

namespace App\Http\Controllers;

use App\Models\TahunAkademik;
use App\Models\VerumKelas;
use App\Models\VerumPostingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VerumController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();
        $semuaKelas = collect(); 

        if (!$tahunAkademikAktif) {
            return view('verum.index', ['semuaKelas' => $semuaKelas])
                   ->with('error', 'Saat ini tidak ada Tahun Akademik yang aktif.');
        }

        if ($user->role == 'dosen') {
            $semuaKelas = VerumKelas::where('dosen_id', $user->dosen->id)
                                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->with('mataKuliah')
                                ->get();
        } elseif ($user->role == 'mahasiswa') {
            $krsMataKuliahIds = $user->mahasiswa->mataKuliahs()->pluck('mata_kuliahs.id');

            $semuaKelas = VerumKelas::whereIn('mata_kuliah_id', $krsMataKuliahIds)
                                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->with('mataKuliah', 'dosen.user')
                                ->get();
        }

        return view('verum.index', compact('semuaKelas'));
    }

    /**
     * == FUNGSI DIPERBARUI ==
     * Memuat data presensi bersama dengan data lainnya.
     */
    public function show(VerumKelas $verum_kela)
    {
        $verum_kela->load(['materi', 'tugas', 'postingan.user', 'presensi.kehadiran']);
        return view('verum.show', compact('verum_kela'));
    }

    public function create()
    {
        $user = Auth::user();
        $mataKuliahs = $user->dosen->mataKuliahs; 

        return view('verum.create', compact('mataKuliahs'));
    }

    public function store(Request $request)
    {
        $request->validate(['mata_kuliah_id' => 'required|exists:mata_kuliahs,id', 'nama_kelas' => 'required|string|max:255', 'deskripsi' => 'nullable|string']);
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();
        if (!$tahunAkademikAktif) {
            return back()->with('error', 'Tidak dapat membuat kelas karena tidak ada tahun akademik yang aktif.');
        }
        VerumKelas::create(['mata_kuliah_id' => $request->mata_kuliah_id, 'nama_kelas' => $request->nama_kelas, 'deskripsi' => $request->deskripsi, 'dosen_id' => Auth::user()->dosen->id, 'tahun_akademik_id' => $tahunAkademikAktif->id, 'kode_kelas' => Str::upper(Str::random(6)),]);
        return redirect()->route('verum.index')->with('success', 'Kelas Verum berhasil dibuat.');
    }

    public function storePost(Request $request, VerumKelas $verum_kela)
    {
        $request->validate(['konten' => 'required|string']);
        VerumPostingan::create(['kelas_id' => $verum_kela->id, 'user_id' => Auth::id(), 'konten' => $request->konten]);
        return back()->with('success', 'Postingan berhasil ditambahkan.');
    }
}
