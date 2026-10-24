<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KrsController extends Controller
{
    private function hitungBatasSks(Mahasiswa $mahasiswa): array
    {
        $ipk = $mahasiswa->hitungIpk();

        $max_sks = 15;
        if ($ipk == 0.00 && $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->doesntExist()) {
            $max_sks = 24;
        } elseif ($ipk >= 3.00) {
            $max_sks = 24;
        } elseif ($ipk >= 2.50) {
            $max_sks = 21;
        } elseif ($ipk >= 2.00) {
            $max_sks = 18;
        }

        return ['ipk' => $ipk, 'max_sks' => $max_sks];
    }

    private function validateKrsRules(Mahasiswa $mahasiswa, TahunAkademik $periodeAktif, array $mata_kuliah_ids, int $max_sks): array
    {
        if (empty($mata_kuliah_ids)) {
            return [];
        }

        $allowedSemesters = in_array($periodeAktif->semester, ['Ganjil', 'Gasal']) ? [1, 3, 5, 7] : [2, 4, 6, 8];
        $mk_dipilih = MataKuliah::with(['prasyarats', 'jadwals'])->whereIn('id', $mata_kuliah_ids)->get();

        if ($mk_dipilih->count() !== count(array_unique($mata_kuliah_ids))) {
            return ['Terdapat mata kuliah yang tidak valid di dalam pilihan Anda.'];
        }

        foreach ($mk_dipilih as $mk) {
            if (!in_array((int) $mk->semester, $allowedSemesters)) {
                return ["Mata kuliah {$mk->nama_mk} (Semester {$mk->semester}) tidak ditawarkan pada semester {$periodeAktif->semester}."];
            }
        }

        $sks_diambil = $mk_dipilih->sum('sks');
        if ($sks_diambil > $max_sks) {
            return ["Total SKS yang dipilih ({$sks_diambil} SKS) melebihi batas maksimal SKS Anda ({$max_sks} SKS)."];
        }

        $mk_lulus_ids = $mahasiswa->mataKuliahs()
            ->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])
            ->pluck('mata_kuliahs.id')
            ->toArray();

        $error_prasyarat = [];
        foreach ($mk_dipilih as $mk) {
            foreach ($mk->prasyarats as $prasyarat) {
                if (!in_array($prasyarat->id, $mk_lulus_ids)) {
                    $error_prasyarat[] = "Syarat ambil {$mk->nama_mk}: Wajib lulus {$prasyarat->nama_mk}.";
                }
            }
        }

        if (!empty($error_prasyarat)) {
            return $error_prasyarat;
        }

        $jadwalTerpilih = [];
        foreach ($mk_dipilih as $mk) {
            foreach ($mk->jadwals as $jadwal) {
                foreach ($jadwalTerpilih as $j) {
                    if (
                        $jadwal->hari == $j['hari'] &&
                        $jadwal->jam_mulai < $j['jam_selesai'] &&
                        $jadwal->jam_selesai > $j['jam_mulai']
                    ) {
                        return ["Jadwal bentrok pada hari {$jadwal->hari}: {$mk->nama_mk} vs {$j['nama_mk']}."];
                    }
                }
                $jadwalTerpilih[] = [
                    'hari' => $jadwal->hari,
                    'jam_mulai' => $jadwal->jam_mulai,
                    'jam_selesai' => $jadwal->jam_selesai,
                    'mk_id' => $mk->id,
                    'nama_mk' => $mk->nama_mk,
                ];
            }
        }

        return [];
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan.');
        }

        if ($mahasiswa->status_mahasiswa !== 'Aktif') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses KRS Ditolak: Status akademik Anda saat ini adalah ' . strtoupper($mahasiswa->status_mahasiswa) . '. Hanya mahasiswa berstatus AKTIF yang dapat mengisi KRS.');
        }

        if ($mahasiswa->status_evaluasi === 'belum_isi') {
            return redirect()->route('evaluasi.index')->with('error', 'Akses Ditolak: Silakan selesaikan pengisian Evaluasi Dosen (EDOM) terlebih dahulu sebelum mengakses KRS.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        $krsSemesterIni = $mahasiswa->mataKuliahs()
                                    ->wherePivot('tahun_akademik_id', $periodeAktif->id)
                                    ->exists();

        if (!$krsSemesterIni && $mahasiswa->status_krs === 'Disetujui') {
            $mahasiswa->status_krs = 'Belum Mengajukan';
            $mahasiswa->save();
        }

        $isLocked = ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni);

        $batas = $this->hitungBatasSks($mahasiswa);
        $ipk = $batas['ipk'];
        $max_sks = $batas['max_sks'];

        $mk_lulus_ids = $mahasiswa->mataKuliahs()
                                 ->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])
                                 ->pluck('mata_kuliahs.id')
                                 ->toArray();

        $allowedSemesters = in_array($periodeAktif->semester, ['Ganjil', 'Gasal']) ? [1, 3, 5, 7] : [2, 4, 6, 8];

        $mata_kuliahs = MataKuliah::with(['prasyarats', 'jadwals'])
            ->whereIn('semester', $allowedSemesters)
            ->orderBy('semester')
            ->orderBy('kode_mk')
            ->get();

        $mk_diambil_ids = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $periodeAktif->id)
            ->pluck('mata_kuliahs.id')
            ->toArray();

        return view('krs.index', [
            'mahasiswa' => $mahasiswa,
            'mata_kuliahs' => $mata_kuliahs,
            'mk_diambil_ids' => $mk_diambil_ids,
            'ipk' => $ipk,
            'max_sks' => $max_sks,
            'mk_lulus_ids' => $mk_lulus_ids,
            'periodeAktif' => $periodeAktif,
            'isLocked' => $isLocked,
        ]);
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan.');
        }

        if ($mahasiswa->status_mahasiswa !== 'Aktif') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses Ditolak: Status akademik Anda adalah ' . strtoupper($mahasiswa->status_mahasiswa) . '.');
        }

        if ($mahasiswa->status_evaluasi === 'belum_isi') {
            return redirect()->route('evaluasi.index')->with('error', 'Akses Ditolak: Anda tidak dapat menyimpan KRS sebelum Evaluasi Dosen (EDOM) diselesaikan.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();
        
        $krsSemesterIni = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
        if ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni) {
            return redirect()->route('krs.index')->with('error', 'Gagal menyimpan. KRS Anda sudah final dan disetujui.');
        }

        $request->validate([
            'mata_kuliahs' => 'nullable|array',
            'mata_kuliahs.*' => 'integer|exists:mata_kuliahs,id',
        ]);

        $mata_kuliah_ids = array_values(array_unique($request->input('mata_kuliahs', [])));
        $batas = $this->hitungBatasSks($mahasiswa);

        $ruleErrors = $this->validateKrsRules($mahasiswa, $periodeAktif, $mata_kuliah_ids, $batas['max_sks']);
        if (!empty($ruleErrors)) {
            throw ValidationException::withMessages(['mata_kuliahs' => $ruleErrors]);
        }
        
        $existingKrs = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->get();
        $gradesToKeep = [];
        foreach ($existingKrs as $existingMk) {
            if (!is_null($existingMk->pivot->nilai)) {
                $gradesToKeep[$existingMk->id] = $existingMk->pivot->nilai;
            }
        }

        $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->detach();
        
        $attachData = [];
        foreach ($mata_kuliah_ids as $mk_id) {
            $attachData[$mk_id] = [
                'tahun_akademik_id' => $periodeAktif->id,
                'nilai' => $gradesToKeep[$mk_id] ?? null,
            ];
        }

        if (!empty($attachData)) {
            $mahasiswa->mataKuliahs()->attach($attachData);
            $mahasiswa->status_krs = 'Menunggu Persetujuan';
        } else {
            $mahasiswa->status_krs = 'Belum Mengajukan';
        }
        $mahasiswa->save();

        return redirect()->route('krs.index')->with('success', 'KRS berhasil disimpan.');
    }

    public function destroy($id)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa || $mahasiswa->status_mahasiswa !== 'Aktif') {
            abort(403, 'Akses ditolak.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->first();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode akademik yang aktif.');
        }
        
        $krsSemesterIni = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
        if ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus. KRS sudah divalidasi oleh Dosen Wali / Kaprodi.');
        }
        
        $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->detach($id);
        
        return redirect()->back()->with('success', 'Mata kuliah berhasil dihapus dari KRS.');
    }

    // =========================================================================
    // FUNGSI API UNTUK MOBILE (DOSEN & MAHASISWA)
    // =========================================================================

    public function getPerluValidasiApi(Request $request)
    {
        $user = $request->user();
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        $query = Mahasiswa::where('status_mahasiswa', 'Aktif')
            ->where('status_krs', 'Menunggu Persetujuan');

        if ($periodeAktif) {
            $query->whereHas('mataKuliahs', function ($q) use ($periodeAktif) {
                $q->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeAktif->id);
            });
        }

        if (!$user->hasRole('admin')) {
            if (!$user->dosen) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }
            $dosenId = $user->dosen->id;
            $prodiKaprodiIds = ProgramStudi::where('kaprodi_dosen_id', $dosenId)->pluck('id')->toArray();

            $query->where(function ($q) use ($dosenId, $prodiKaprodiIds) {
                $q->where('dosen_wali_id', $dosenId);
                if (!empty($prodiKaprodiIds)) {
                    $q->orWhereIn('program_studi_id', $prodiKaprodiIds);
                }
            });
        }

        $mahasiswas = $query->select('id', 'nama_lengkap', 'nim', 'status_krs')->orderBy('nama_lengkap')->get();

        $formattedData = $mahasiswas->map(function ($mhs) {
            return [
                'id' => $mhs->id,
                'mahasiswa' => [
                    'name' => $mhs->nama_lengkap,
                    'nama_lengkap' => $mhs->nama_lengkap,
                    'nim' => $mhs->nim,
                ]
            ];
        });

        return response()->json($formattedData);
    }

    public function validasiKrsApi(Request $request, $id)
    {
        $user = $request->user();
        $request->validate(['status' => 'required|in:Setujui,Tolak']);
        $mahasiswa = Mahasiswa::findOrFail($id);

        if (!$user->hasRole('admin')) {
            if (!$user->dosen) {
                return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
            }
            $isDosenWali = ((int) $mahasiswa->dosen_wali_id === (int) $user->dosen->id);
            $isKaprodiProdi = ProgramStudi::where('id', $mahasiswa->program_studi_id)
                ->where('kaprodi_dosen_id', $user->dosen->id)
                ->exists();

            if (!$isDosenWali && !$isKaprodiProdi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki wewenang untuk memvalidasi KRS mahasiswa ini.'
                ], 403);
            }
        }
        
        $mahasiswa->status_krs = ($request->status === 'Setujui') ? 'Disetujui' : 'Ditolak';
        $mahasiswa->save();

        return response()->json([
            'status' => 'success',
            'message' => 'KRS Mahasiswa berhasil ' . $mahasiswa->status_krs
        ]);
    }
    
    public function getFormKrsApi(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan.'], 403);
        }

        if ($mahasiswa->status_mahasiswa !== 'Aktif') {
            return response()->json(['message' => 'Status akademik Anda adalah ' . $mahasiswa->status_mahasiswa . '. Hanya mahasiswa Aktif yang dapat mengisi KRS.'], 403);
        }

        if ($mahasiswa->status_evaluasi === 'belum_isi') {
            return response()->json(['message' => 'Silakan isi evaluasi dosen (EDOM) terlebih dahulu.'], 403);
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->first();
        if (!$periodeAktif) {
            return response()->json(['message' => 'Tidak ada periode aktif'], 400);
        }

        $krsSemesterIni = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
        if (!$krsSemesterIni && $mahasiswa->status_krs === 'Disetujui') {
            $mahasiswa->status_krs = 'Belum Mengajukan';
            $mahasiswa->save();
        }

        $batas = $this->hitungBatasSks($mahasiswa);

        $allowedSemesters = in_array($periodeAktif->semester, ['Ganjil', 'Gasal']) ? [1, 3, 5, 7] : [2, 4, 6, 8];
        
        $mata_kuliahs = MataKuliah::with(['jadwals' => function ($q) {
            $q->select('mata_kuliah_id', 'hari', 'jam_mulai', 'jam_selesai');
        }])->whereIn('semester', $allowedSemesters)->orderBy('semester')->orderBy('kode_mk')->get();

        $mk_diambil_ids = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $periodeAktif->id)
            ->pluck('mata_kuliahs.id')
            ->toArray();

        return response()->json([
            'ipk' => $batas['ipk'],
            'max_sks' => $batas['max_sks'],
            'mk_diambil_ids' => $mk_diambil_ids,
            'mata_kuliahs' => $mata_kuliahs,
            'status_krs' => $mahasiswa->status_krs
        ]);
    }

    public function submitKrsApi(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan.'], 403);
        }

        if ($mahasiswa->status_mahasiswa !== 'Aktif') {
            return response()->json(['message' => 'Status akademik Anda adalah ' . $mahasiswa->status_mahasiswa . '. Hanya mahasiswa Aktif yang dapat mengisi KRS.'], 403);
        }

        if ($mahasiswa->status_evaluasi === 'belum_isi') {
            return response()->json(['message' => 'Silakan isi evaluasi dosen (EDOM) terlebih dahulu.'], 403);
        }
        
        $periodeAktif = TahunAkademik::where('is_active', true)->first();
        if (!$periodeAktif) {
            return response()->json(['message' => 'Tidak ada periode akademik yang aktif.'], 400);
        }

        $krsSemesterIni = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
        if ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni) {
            return response()->json(['message' => 'KRS sudah disetujui, tidak bisa diubah.'], 403);
        }

        $request->validate([
            'mata_kuliahs' => 'nullable|array',
            'mata_kuliahs.*' => 'integer|exists:mata_kuliahs,id',
        ]);

        $mata_kuliah_ids = array_values(array_unique($request->input('mata_kuliahs', [])));
        $batas = $this->hitungBatasSks($mahasiswa);

        $ruleErrors = $this->validateKrsRules($mahasiswa, $periodeAktif, $mata_kuliah_ids, $batas['max_sks']);
        if (!empty($ruleErrors)) {
            return response()->json([
                'status' => 'error',
                'message' => implode(' ', $ruleErrors),
                'errors' => ['mata_kuliahs' => $ruleErrors]
            ], 422);
        }

        $existingKrs = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->get();
        $gradesToKeep = [];
        foreach ($existingKrs as $existingMk) {
            if (!is_null($existingMk->pivot->nilai)) {
                $gradesToKeep[$existingMk->id] = $existingMk->pivot->nilai;
            }
        }

        $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->detach();
        
        $attachData = [];
        foreach ($mata_kuliah_ids as $mk_id) {
            $attachData[$mk_id] = [
                'tahun_akademik_id' => $periodeAktif->id,
                'nilai' => $gradesToKeep[$mk_id] ?? null
            ];
        }

        if (!empty($attachData)) {
            $mahasiswa->mataKuliahs()->attach($attachData);
            $mahasiswa->status_krs = 'Menunggu Persetujuan';
        } else {
            $mahasiswa->status_krs = 'Belum Mengajukan';
        }
        $mahasiswa->save();

        return response()->json(['message' => 'KRS Berhasil Disimpan']);
    }
}