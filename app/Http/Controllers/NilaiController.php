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
     * Helper untuk mengecek apakah user berhak mengelola nilai suatu mata kuliah.
     */
    private function canManageNilai($user, MataKuliah $mataKuliah): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('dosen') && $user->dosen) {
            if ((int) $user->dosen->id === (int) $mataKuliah->dosen_id) {
                return true;
            }

            return DB::table('dosen_mata_kuliah')
                ->where('mata_kuliah_id', $mataKuliah->id)
                ->where('dosen_id', $user->dosen->id)
                ->exists();
        }

        return false;
    }

    public function index(): View
    {
        $user = Auth::user();

        if (!$user->hasRole('admin') && (!$user->hasRole('dosen') || !$user->dosen)) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Administrator atau Dosen Pengampu.');
        }
        
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();
        
        $mkAktifSaatIniIds = [];
        if ($tahunAkademikAktif) {
            $mkAktifSaatIniIds = DB::table('mahasiswa_mata_kuliah')
                                   ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                                   ->distinct()
                                   ->pluck('mata_kuliah_id')
                                   ->toArray();
        }

        // Jika Admin: tampilkan semua mata kuliah yang memiliki peserta KRS aktif (atau semua MK jika belum ada KRS)
        if ($user->hasRole('admin')) {
            $query = MataKuliah::with('dosen')->orderBy('semester')->orderBy('kode_mk');
            if (!empty($mkAktifSaatIniIds)) {
                $query->whereIn('id', $mkAktifSaatIniIds);
            }
            $mata_kuliahs = $query->get();
        } else {
            $pivotMkIds = DB::table('dosen_mata_kuliah')
                ->where('dosen_id', $user->dosen->id)
                ->pluck('mata_kuliah_id')
                ->toArray();

            $mata_kuliahs = MataKuliah::with('dosen')
                ->where(function ($query) use ($user, $pivotMkIds) {
                    $query->where('dosen_id', $user->dosen->id)
                          ->orWhereIn('id', $pivotMkIds);
                })
                ->whereIn('id', $mkAktifSaatIniIds)
                ->orderBy('semester')
                ->get();
        }
        
        return view('nilai.index', compact('mata_kuliahs'));
    }

    public function show(MataKuliah $mataKuliah): View
    {
        $user = Auth::user();

        if (!$this->canManageNilai($user, $mataKuliah)) {
            abort(403, 'Anda tidak berhak menginput nilai untuk mata kuliah ini.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();

        if (!$tahunAkademikAktif) {
            $mataKuliah->setRelation('mahasiswas', collect());
            session()->flash('error', 'Tidak ada Tahun Akademik yang aktif.');
        } else {
            $mataKuliah->load(['mahasiswas' => function ($query) use ($tahunAkademikAktif) {
                $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademikAktif->id)
                      ->where('mahasiswas.status_krs', 'Disetujui') 
                      ->orderBy('nama_lengkap', 'asc');
            }]);
        }
        
        return view('nilai.show', compact('mataKuliah'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'nilai' => 'nullable|array',
            'nilai.*' => 'nullable|string|max:2|in:A,B,C,D,E,a,b,c,d,e',
        ]);
    
        $mataKuliah = MataKuliah::findOrFail($request->mata_kuliah_id);
        $user = Auth::user();

        if (!$this->canManageNilai($user, $mataKuliah)) {
            abort(403, 'Akses ditolak. Validasi pengampu mata kuliah gagal.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        // Hanya izinkan penyimpanan nilai untuk mahasiswa yang KRS-nya Disetujui di semester aktif
        $validMahasiswaIds = $mataKuliah->mahasiswas()
            ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
            ->where('mahasiswas.status_krs', 'Disetujui')
            ->pluck('mahasiswas.id')
            ->toArray();
        
        if ($request->has('nilai')) {
            foreach ($request->nilai as $mahasiswa_id => $nilai) {
                if (in_array((int) $mahasiswa_id, $validMahasiswaIds)) {
                    $mataKuliah->mahasiswas()
                        ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
                        ->updateExistingPivot($mahasiswa_id, [
                            'nilai' => $nilai ? strtoupper(trim($nilai)) : null
                        ]);
                }
            }
        }

        return redirect()->route('nilai.show', $mataKuliah->id)->with('success', 'Nilai mahasiswa berhasil disimpan!');
    }
}