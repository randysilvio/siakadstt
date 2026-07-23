<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NilaiController extends Controller
{
    /**
     * Menampilkan daftar mata kuliah (HANYA UNTUK DOSEN PENGAMPU DI SEMESTER AKTIF).
     */
    public function index(): View
    {
        $user = Auth::user();

        // 1. BLOKIR ADMIN & Peran Lain
        if (!$user->hasRole('dosen') || !$user->dosen) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Dosen Pengampu.');
        }
        
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();
        
        // 2. Filter Ketat: Hanya tampilkan kelas yang terdaftar di semester aktif ini (baik dosen utama maupun tim)
        $mkIdSemesterIni = [];
        if ($tahunAkademikAktif) {
            $mkIdSemesterIni = DB::table('mahasiswa_mata_kuliah')
                                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                ->distinct()
                                ->pluck('mata_kuliah_id')
                                ->toArray();
        }
        
        $pivotMkIds = DB::table('dosen_mata_kuliah')
            ->where('dosen_id', $user->dosen->id)
            ->pluck('mata_kuliah_id')
            ->toArray();

        $mata_kuliahs = MataKuliah::where(function($query) use ($user, $pivotMkIds) {
                $query->where('dosen_id', $user->dosen->id)
                      ->orWhereIn('id', $pivotMkIds);
            })
            ->whereIn('id', $mkIdSemesterIni)
            ->get();
        
        return view('nilai.index', compact('mata_kuliahs'));
    }

    /**
     * Menampilkan form input nilai (HANYA DOSEN PENGAMPU).
     */
    public function show(MataKuliah $mataKuliah): View
    {
        $user = Auth::user();

        $isTeam = DB::table('dosen_mata_kuliah')
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->where('dosen_id', $user->dosen->id)
            ->exists();

        // 1. Validasi Kepemilikan Mata Kuliah (Dosen Utama atau Tim)
        if (!$user->dosen || ($user->dosen->id !== $mataKuliah->dosen_id && !$isTeam)) {
            abort(403, 'Anda tidak berhak menginput nilai untuk mata kuliah ini.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();

        if (!$tahunAkademikAktif) {
            $mataKuliah->setRelation('mahasiswas', collect());
            session()->flash('error', 'Tidak ada Tahun Akademik yang aktif.');
        } else {
            // Load mahasiswa di semester aktif saja DAN yang KRS-nya sudah DISETUJUI
            $mataKuliah->load(['mahasiswas' => function ($query) use ($tahunAkademikAktif) {
                $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademikAktif->id)
                      ->where('mahasiswas.status_krs', 'Disetujui') 
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
        
        $isTeam = DB::table('dosen_mata_kuliah')
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->where('dosen_id', $user->dosen->id)
            ->exists();

        // 1. Validasi Kepemilikan (Double Check sebelum save)
        if (!$user->dosen || ($user->dosen->id !== $mataKuliah->dosen_id && !$isTeam)) {
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