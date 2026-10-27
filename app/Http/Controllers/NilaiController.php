<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Models\VerumKelas;
use App\Services\AcademicRuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NilaiController extends Controller
{
    protected AcademicRuleService $academicService;

    public function __construct(AcademicRuleService $academicService)
    {
        $this->academicService = $academicService;
    }

    public static function konversiAngkaKeHuruf(float $angka): string
    {
        return app(AcademicRuleService::class)->konversiAngkaKeHuruf($angka);
    }

    public function index(): View
    {
        $user = Auth::user();

        if (!$user->hasRole('admin') && (!$user->hasRole('dosen') || !$user->dosen)) {
            abort(403, 'Akses ditolak.');
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

            $mata_kuliahs = MataKuliah::where(function ($q) use ($user, $pivotMkIds) {
                    $q->where('dosen_id', $user->dosen->id)
                      ->orWhereIn('id', $pivotMkIds);
                })
                ->where(function ($q) use ($mkAktifSaatIniIds) {
                    $q->whereHas('kurikulum', fn($k) => $k->where('is_active', true));
                    if (!empty($mkAktifSaatIniIds)) {
                        $q->orWhereIn('id', $mkAktifSaatIniIds);
                    }
                })
                ->orderBy('semester')
                ->orderBy('kode_mk')
                ->get();
        }

        return view('nilai.index', [
            'mata_kuliahs' => $mata_kuliahs,
            'tahunAkademikAktif' => $tahunAkademikAktif
        ]);
    }

    public function show(MataKuliah $mataKuliah): View
    {
        $user = Auth::user();

        if (!Gate::forUser($user)->allows('kelolaNilai', $mataKuliah)) {
            abort(403, 'Anda tidak memiliki akses untuk mengisi nilai mata kuliah ini.');
        }
        
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        $mataKuliah->load(['mahasiswas' => function ($query) use ($tahunAkademikAktif) {
            $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademikAktif->id)
                  ->whereIn('mahasiswas.id', \App\Models\KrsPersetujuan::idsDisetujui((int) $tahunAkademikAktif->id))
                  ->orderBy('nama_lengkap', 'asc');
        }]);

        $hasVerumKelas = VerumKelas::where('mata_kuliah_id', $mataKuliah->id)
            ->where('tahun_akademik_id', $tahunAkademikAktif->id)
            ->exists();

        return view('nilai.show', compact('mataKuliah', 'tahunAkademikAktif', 'hasVerumKelas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mata_kuliah_id'    => 'required|exists:mata_kuliahs,id',
            'peserta'           => 'nullable|array',
            'peserta.*'         => 'integer',
            'nilai'             => 'nullable|array',
            'nilai.*'           => 'nullable|string|in:A,B,C,D,E',
            'huruf_manual'      => 'nullable|array',
            'huruf_manual.*'    => 'nullable|in:0,1',
            'nilai_kehadiran'   => 'nullable|array',
            'nilai_kehadiran.*' => 'nullable|numeric|min:0|max:100',
            'nilai_tugas'       => 'nullable|array',
            'nilai_tugas.*'     => 'nullable|numeric|min:0|max:100',
            'nilai_uts'         => 'nullable|array',
            'nilai_uts.*'       => 'nullable|numeric|min:0|max:100',
            'nilai_uas'         => 'nullable|array',
            'nilai_uas.*'       => 'nullable|numeric|min:0|max:100',
        ]);

        $mataKuliah = MataKuliah::findOrFail($request->input('mata_kuliah_id'));
        $user = Auth::user();

        if (!Gate::forUser($user)->allows('kelolaNilai', $mataKuliah)) {
            abort(403, 'Anda tidak berhak menyimpan nilai untuk mata kuliah ini.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();
        if (!$tahunAkademikAktif) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Akademik yang aktif.');
        }

        $enrolledMahasiswaIds = $mataKuliah->mahasiswas()
            ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
            ->whereIn('mahasiswas.id', \App\Models\KrsPersetujuan::idsDisetujui((int) $tahunAkademikAktif->id))
            ->pluck('mahasiswas.id')
            ->toArray();

        $inputHuruf  = $request->input('nilai', []);
        $inputManual = $request->input('huruf_manual', []);
        $inputHadir  = $request->input('nilai_kehadiran', []);
        $inputTugas  = $request->input('nilai_tugas', []);
        $inputUts    = $request->input('nilai_uts', []);
        $inputUas    = $request->input('nilai_uas', []);

        // Hanya peserta yang benar-benar ada di form yang diproses. Peserta yang tidak terkirim
        // (mis. baru disetujui setelah halaman dibuka) tidak boleh ikut dikosongkan nilainya.
        $pesertaForm = collect($request->input('peserta', []))->map(fn ($id) => (int) $id);
        if ($pesertaForm->isEmpty()) {
            $pesertaForm = collect(array_keys($inputHuruf + $inputHadir + $inputTugas + $inputUts + $inputUas))->map(fn ($id) => (int) $id);
        }
        $targetIds = array_values(array_intersect($enrolledMahasiswaIds, $pesertaForm->all()));

        DB::transaction(function () use ($targetIds, $mataKuliah, $tahunAkademikAktif, $inputHuruf, $inputManual, $inputHadir, $inputTugas, $inputUts, $inputUas) {
            foreach ($targetIds as $mhsId) {
                $hadir = isset($inputHadir[$mhsId]) && $inputHadir[$mhsId] !== '' ? (float) $inputHadir[$mhsId] : null;
                $tugas = isset($inputTugas[$mhsId]) && $inputTugas[$mhsId] !== '' ? (float) $inputTugas[$mhsId] : null;
                $uts   = isset($inputUts[$mhsId])   && $inputUts[$mhsId] !== ''   ? (float) $inputUts[$mhsId]   : null;
                $uas   = isset($inputUas[$mhsId])   && $inputUas[$mhsId] !== ''   ? (float) $inputUas[$mhsId]   : null;
                $hurufInput = !empty($inputHuruf[$mhsId]) ? strtoupper(trim($inputHuruf[$mhsId])) : null;
                $isManual   = ($inputManual[$mhsId] ?? '0') === '1' && $hurufInput !== null;

                $hasil = $this->academicService->tentukanNilai($hadir, $tugas, $uts, $uas, $isManual ? $hurufInput : null);

                DB::table('mahasiswa_mata_kuliah')
                    ->where('mahasiswa_id', $mhsId)
                    ->where('mata_kuliah_id', $mataKuliah->id)
                    ->where('tahun_akademik_id', $tahunAkademikAktif->id)
                    ->update([
                        'nilai_kehadiran'   => $hadir,
                        'nilai_tugas'       => $tugas,
                        'nilai_uts'         => $uts,
                        'nilai_uas'         => $uas,
                        'nilai_akhir_angka' => $hasil['angka'],
                        'nilai'             => $hasil['huruf'],
                        'nilai_manual'      => $isManual,
                        'updated_at'        => now(),
                    ]);
            }
        });

        return redirect()->route('nilai.show', $mataKuliah->id)
            ->with('success', 'Nilai mahasiswa berhasil disimpan.');
    }

    public function syncVerum(MataKuliah $mataKuliah): RedirectResponse
    {
        $user = Auth::user();

        if (!Gate::forUser($user)->allows('kelolaNilai', $mataKuliah)) {
            abort(403, 'Akses ditolak.');
        }

        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        $updatedCount = $this->academicService->syncNilaiVerumKeKrs($mataKuliah, $tahunAkademikAktif);

        if ($updatedCount === 0) {
            return redirect()->route('nilai.show', $mataKuliah->id)
                ->with('error', 'Belum ditemukan ruang kelas Verum atau peserta aktif untuk mata kuliah ini.');
        }

        return redirect()->route('nilai.show', $mataKuliah->id)
            ->with('success', "Data kehadiran dan tugas dari kelas Verum berhasil disinkronkan untuk {$updatedCount} mahasiswa.");
    }
}