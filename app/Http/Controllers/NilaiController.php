<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NilaiController extends Controller
{
    /**
     * Menampilkan daftar mata kuliah (HANYA UNTUK DOSEN PENGAMPU).
     */
    public function index(): View
    {
        $user = Auth::user();

        // 1. BLOKIR ADMIN & Peran Lain
        if (!$user->hasRole('dosen') || !$user->dosen) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Dosen Pengampu.');
        }

        // 2. Hanya ambil mata kuliah milik dosen tersebut
        $mata_kuliahs = MataKuliah::where('dosen_id', $user->dosen->id)->get();
        
        return view('nilai.index', compact('mata_kuliahs'));
    }

    /**
     * Menampilkan form input nilai (HANYA DOSEN PENGAMPU).
     */
    public function show(MataKuliah $mataKuliah): View
    {
        $user = Auth::user();

        // 1. Validasi Kepemilikan Mata Kuliah
        if (!$user->dosen || $user->dosen->id !== $mataKuliah->dosen_id) {
            abort(403, 'Anda tidak berhak menginput nilai untuk mata kuliah ini.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();

        if (!$tahunAkademikAktif) {
            $mataKuliah->setRelation('mahasiswas', collect());
            session()->flash('error', 'Tidak ada Tahun Akademik yang aktif.');
        } else {
            // Load mahasiswa di semester aktif saja
            $mataKuliah->load(['mahasiswas' => function ($query) use ($tahunAkademikAktif) {
                $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademikAktif->id)
                      ->orderBy('nama_lengkap', 'asc');
            }]);
        }
        
        return view('nilai.show', compact('mataKuliah'));
    }

    /**
     * Menyimpan nilai (HANYA DOSEN PENGAMPU).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'nilai.*' => 'nullable|string|max:2|in:A,B,C,D,E',
        ]);
    
        $mataKuliah = MataKuliah::findOrFail($request->mata_kuliah_id);
        $user = Auth::user();

        // 1. Validasi Kepemilikan (Double Check sebelum save)
        if (!$user->dosen || $user->dosen->id !== $mataKuliah->dosen_id) {
            abort(403, 'Akses ditolak. Validasi dosen gagal.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->firstOrFail();
        
        if ($request->has('nilai')) {
            foreach ($request->nilai as $mahasiswa_id => $nilai) {
                $mataKuliah->mahasiswas()
                    ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
                    ->updateExistingPivot($mahasiswa_id, ['nilai' => $nilai ? strtoupper($nilai) : null]);
            }
        }

        return redirect()->route('nilai.show', $mataKuliah->id)->with('success', 'Nilai berhasil disimpan!');
    }
}