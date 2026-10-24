<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Models\VerumKelas;
use App\Models\VerumPengumpulan;
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

    /**
     * Konversi nilai angka (0 - 100) ke huruf mutu resmi (A, B, C, D, E).
     */
    public static function konversiAngkaKeHuruf(float $angka): string
    {
        if ($angka >= 80) return 'A';
        if ($angka >= 70) return 'B';
        if ($angka >= 60) return 'C';
        if ($angka >= 50) return 'D';
        return 'E';
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
                ->where(function ($q) use ($mkAktifSaatIniIds) {
                    $q->whereIn('id', $mkAktifSaatIniIds)
                      ->orWhereHas('kurikulum', fn($k) => $k->where('is_active', true));
                })
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
        $hasVerumKelas = false;

        if (!$tahunAkademikAktif) {
            $mataKuliah->setRelation('mahasiswas', collect());
            session()->flash('error', 'Tidak ada Tahun Akademik yang aktif.');
        } else {
            $mataKuliah->load(['mahasiswas' => function ($query) use ($tahunAkademikAktif) {
                $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademikAktif->id)
                      ->where('mahasiswas.status_krs', 'Disetujui') 
                      ->orderBy('nama_lengkap', 'asc');
            }]);

            $hasVerumKelas = VerumKelas::where('mata_kuliah_id', $mataKuliah->id)
                ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                ->exists();
        }
        
        return view('nilai.show', compact('mataKuliah', 'tahunAkademikAktif', 'hasVerumKelas'));
    }

    /**
     * Menyimpan nilai komponen OBE (Kehadiran, Tugas, UTS, UAS) dan/atau Nilai Huruf Akhir.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mata_kuliah_id'    => 'required|exists:mata_kuliahs,id',
            'nilai_kehadiran'   => 'nullable|array',
            'nilai_kehadiran.*' => 'nullable|numeric|min:0|max:100',
            'nilai_tugas'       => 'nullable|array',
            'nilai_tugas.*'     => 'nullable|numeric|min:0|max:100',
            'nilai_uts'         => 'nullable|array',
            'nilai_uts.*'       => 'nullable|numeric|min:0|max:100',
            'nilai_uas'         => 'nullable|array',
            'nilai_uas.*'       => 'nullable|numeric|min:0|max:100',
            'nilai'             => 'nullable|array',
            'nilai.*'           => 'nullable|string|max:2|in:A,B,C,D,E,a,b,c,d,e',
        ]);
    
        $mataKuliah = MataKuliah::findOrFail($request->mata_kuliah_id);
        $user = Auth::user();

        if (!$this->canManageNilai($user, $mataKuliah)) {
            abort(403, 'Akses ditolak. Validasi pengampu mata kuliah gagal.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        $validMahasiswaIds = $mataKuliah->mahasiswas()
            ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
            ->where('mahasiswas.status_krs', 'Disetujui')
            ->pluck('mahasiswas.id')
            ->toArray();

        $kehadiranArr = $request->input('nilai_kehadiran', []);
        $tugasArr     = $request->input('nilai_tugas', []);
        $utsArr       = $request->input('nilai_uts', []);
        $uasArr       = $request->input('nilai_uas', []);
        $hurufArr     = $request->input('nilai', []);

        DB::transaction(function () use ($mataKuliah, $tahunAkademikAktif, $validMahasiswaIds, $kehadiranArr, $tugasArr, $utsArr, $uasArr, $hurufArr) {
            foreach ($validMahasiswaIds as $mhsId) {
                $k = isset($kehadiranArr[$mhsId]) && $kehadiranArr[$mhsId] !== '' ? (float) $kehadiranArr[$mhsId] : null;
                $t = isset($tugasArr[$mhsId]) && $tugasArr[$mhsId] !== '' ? (float) $tugasArr[$mhsId] : null;
                $uts = isset($utsArr[$mhsId]) && $utsArr[$mhsId] !== '' ? (float) $utsArr[$mhsId] : null;
                $uas = isset($uasArr[$mhsId]) && $uasArr[$mhsId] !== '' ? (float) $uasArr[$mhsId] : null;
                $hurufManual = isset($hurufArr[$mhsId]) && $hurufArr[$mhsId] !== '' ? strtoupper(trim($hurufArr[$mhsId])) : null;

                $hasAnyComponent = ($k !== null || $t !== null || $uts !== null || $uas !== null);
                $nilaiAkhirAngka = null;
                $nilaiHurufFinal = $hurufManual;

                if ($hasAnyComponent) {
                    // Rumus Bobot Standar OBE: Kehadiran 10% + Tugas 20% + UTS 30% + UAS 40%
                    $nilaiAkhirAngka = round(
                        (($k ?? 0) * 0.10) +
                        (($t ?? 0) * 0.20) +
                        (($uts ?? 0) * 0.30) +
                        (($uas ?? 0) * 0.40),
                        2
                    );
                    // Jika seluruh komponen utama terisi atau huruf tidak dipilih manual, konversi otomatis dari Nilai Akhir Angka
                    $nilaiHurufFinal = self::konversiAngkaKeHuruf($nilaiAkhirAngka);
                }

                $mataKuliah->mahasiswas()
                    ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
                    ->updateExistingPivot($mhsId, [
                        'nilai_kehadiran'   => $k,
                        'nilai_tugas'       => $t,
                        'nilai_uts'         => $uts,
                        'nilai_uas'         => $uas,
                        'nilai_akhir_angka' => $nilaiAkhirAngka,
                        'nilai'             => $nilaiHurufFinal,
                    ]);
            }
        });

        return redirect()->route('nilai.show', $mataKuliah->id)->with('success', 'Data nilai komponen OBE dan huruf mutu mahasiswa berhasil disimpan!');
    }

    /**
     * [FITUR BARU]: One-Click Sync Kehadiran & Nilai Tugas dari Kelas E-Learning Verum.
     */
    public function syncVerum(MataKuliah $mataKuliah): RedirectResponse
    {
        $user = Auth::user();
        if (!$this->canManageNilai($user, $mataKuliah)) {
            abort(403, 'Akses ditolak.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        $verumKelasList = VerumKelas::with(['presensi.kehadiran', 'tugas'])
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->where('tahun_akademik_id', $tahunAkademikAktif->id)
            ->get();

        if ($verumKelasList->isEmpty()) {
            return back()->with('error', 'Belum ada kelas E-Learning Verum untuk mata kuliah ini pada semester berjalan.');
        }

        $mahasiswas = $mataKuliah->mahasiswas()
            ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
            ->where('mahasiswas.status_krs', 'Disetujui')
            ->get();

        // Kumpulkan seluruh sesi presensi dan ID tugas dari kelas Verum mata kuliah ini
        $allPresensis = $verumKelasList->flatMap->presensi;
        $totalPertemuan = $allPresensis->count();
        $allTugasIds = $verumKelasList->flatMap->tugas->pluck('id')->toArray();
        $totalTugas = count($allTugasIds);

        $countUpdated = 0;

        DB::transaction(function () use ($mataKuliah, $tahunAkademikAktif, $mahasiswas, $allPresensis, $totalPertemuan, $allTugasIds, $totalTugas, &$countUpdated) {
            foreach ($mahasiswas as $mhs) {
                $skorKehadiran = $mhs->pivot->nilai_kehadiran;
                $skorTugas = $mhs->pivot->nilai_tugas;

                // 1. Hitung Persentase Kehadiran (0 - 100) jika ada sesi presensi di Verum
                if ($totalPertemuan > 0) {
                    $hadirCount = 0;
                    foreach ($allPresensis as $sesi) {
                        $isHadir = $sesi->kehadiran
                            ->where('mahasiswa_id', $mhs->id)
                            ->whereIn('status', ['Hadir', 'hadir', 'Izin', 'izin', 'Sakit', 'sakit'])
                            ->isNotEmpty();
                        if ($isHadir) {
                            $hadirCount++;
                        }
                    }
                    $skorKehadiran = round(($hadirCount / $totalPertemuan) * 100, 2);
                }

                // 2. Hitung Rata-rata Nilai Tugas Verum (0 - 100) jika ada tugas di Verum
                if ($totalTugas > 0) {
                    $totalSkorTugas = VerumPengumpulan::whereIn('tugas_id', $allTugasIds)
                        ->where('mahasiswa_id', $mhs->id)
                        ->whereNotNull('nilai')
                        ->sum('nilai');

                    $adaNilaiTugas = VerumPengumpulan::whereIn('tugas_id', $allTugasIds)
                        ->where('mahasiswa_id', $mhs->id)
                        ->whereNotNull('nilai')
                        ->exists();

                    if ($adaNilaiTugas) {
                        $skorTugas = round($totalSkorTugas / $totalTugas, 2);
                    }
                }

                // 3. Hitung ulang Nilai Akhir Angka & Huruf jika ada komponen
                $uts = $mhs->pivot->nilai_uts !== null ? (float) $mhs->pivot->nilai_uts : null;
                $uas = $mhs->pivot->nilai_uas !== null ? (float) $mhs->pivot->nilai_uas : null;

                $nilaiAkhirAngka = null;
                $nilaiHurufFinal = $mhs->pivot->nilai;

                if ($skorKehadiran !== null || $skorTugas !== null || $uts !== null || $uas !== null) {
                    $nilaiAkhirAngka = round(
                        (($skorKehadiran ?? 0) * 0.10) +
                        (($skorTugas ?? 0) * 0.20) +
                        (($uts ?? 0) * 0.30) +
                        (($uas ?? 0) * 0.40),
                        2
                    );
                    $nilaiHurufFinal = self::konversiAngkaKeHuruf($nilaiAkhirAngka);
                }

                $mataKuliah->mahasiswas()
                    ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
                    ->updateExistingPivot($mhs->id, [
                        'nilai_kehadiran'   => $skorKehadiran,
                        'nilai_tugas'       => $skorTugas,
                        'nilai_akhir_angka' => $nilaiAkhirAngka,
                        'nilai'             => $nilaiHurufFinal,
                    ]);

                $countUpdated++;
            }
        });

        return redirect()->route('nilai.show', $mataKuliah->id)
            ->with('success', "Sinkronisasi E-Learning Verum berhasil! Data kehadiran ({$totalPertemuan} sesi) dan tugas ({$totalTugas} tugas) untuk {$countUpdated} mahasiswa telah ditarik ke buku nilai.");
    }
}