<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Services\AcademicRuleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KrsController extends Controller
{
    protected AcademicRuleService $academicService;

    public function __construct(AcademicRuleService $academicService)
    {
        $this->academicService = $academicService;
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Data mahasiswa tidak ditemukan.');
        }

        if ($mahasiswa->status_mahasiswa !== 'Aktif') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses KRS Ditolak: Status akademik Anda saat ini adalah ' . strtoupper($mahasiswa->status_mahasiswa) . '.');
        }

        if ($mahasiswa->status_evaluasi === 'belum_isi') {
            return redirect()->route('evaluasi.index')
                ->with('error', 'Silakan selesaikan pengisian Evaluasi Dosen (EDOM) terlebih dahulu sebelum mengakses KRS.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        $krsExisting = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $periodeAktif->id)
            ->get();

        $krsSemesterIni = $krsExisting->isNotEmpty();

        if (!$krsSemesterIni && $mahasiswa->status_krs === 'Disetujui') {
            $mahasiswa->status_krs = 'Belum Mengajukan';
            $mahasiswa->save();
        }

        $isLocked = ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni);

        $batas = $this->academicService->hitungBatasSks($mahasiswa);
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

        // Hitung jumlah terisi per mata_kuliah_id dan nama_kelas pada semester aktif
        $terisiMap = DB::table('mahasiswa_mata_kuliah')
            ->select('mata_kuliah_id', 'nama_kelas', DB::raw('COUNT(*) as total'))
            ->where('tahun_akademik_id', $periodeAktif->id)
            ->groupBy('mata_kuliah_id', 'nama_kelas')
            ->get()
            ->groupBy('mata_kuliah_id');

        $mk_diambil_ids = $krsExisting->pluck('id')->toArray();
        $kelas_diambil_map = [];
        foreach ($krsExisting as $item) {
            $kelas_diambil_map[$item->id] = $item->pivot->nama_kelas ?? 'A';
        }

        return view('krs.index', [
            'mahasiswa'         => $mahasiswa,
            'mata_kuliahs'      => $mata_kuliahs,
            'mk_diambil_ids'    => $mk_diambil_ids,
            'kelas_diambil_map' => $kelas_diambil_map,
            'terisiMap'         => $terisiMap,
            'ipk'               => $ipk,
            'max_sks'           => $max_sks,
            'mk_lulus_ids'      => $mk_lulus_ids,
            'periodeAktif'      => $periodeAktif,
            'isLocked'          => $isLocked,
        ]);
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa || $mahasiswa->status_mahasiswa !== 'Aktif') {
            abort(403, 'Akses ditolak.');
        }

        if ($mahasiswa->status_evaluasi === 'belum_isi') {
            return redirect()->route('evaluasi.index')
                ->with('error', 'Anda tidak dapat menyimpan KRS sebelum Evaluasi Dosen (EDOM) diselesaikan.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();
        
        $krsSemesterIni = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
        if ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni) {
            return redirect()->route('krs.index')->with('error', 'KRS Anda sudah final dan disetujui.');
        }

        $request->validate([
            'mata_kuliahs'   => 'nullable|array',
            'mata_kuliahs.*' => 'integer|exists:mata_kuliahs,id',
            'nama_kelas'     => 'nullable|array',
            'nama_kelas.*'   => 'nullable|string|max:30',
        ]);

        $mata_kuliah_ids = array_values(array_unique($request->input('mata_kuliahs', [])));
        $pilihanKelas    = $request->input('nama_kelas', []);
        $batas           = $this->academicService->hitungBatasSks($mahasiswa);

        $ruleErrors = $this->academicService->validateKrsRules($mahasiswa, $periodeAktif, $mata_kuliah_ids, $batas['max_sks']);
        if (!empty($ruleErrors)) {
            throw ValidationException::withMessages(['mata_kuliahs' => $ruleErrors]);
        }
        
        // Backup seluruh nilai huruf maupun komponen OBE agar tidak hilang saat revisi KRS
        $existingKrs = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->get();
        $gradesToKeep = [];
        foreach ($existingKrs as $existingMk) {
            $gradesToKeep[$existingMk->id] = [
                'nilai'             => $existingMk->pivot->nilai,
                'nilai_kehadiran'   => $existingMk->pivot->nilai_kehadiran,
                'nilai_tugas'       => $existingMk->pivot->nilai_tugas,
                'nilai_uts'         => $existingMk->pivot->nilai_uts,
                'nilai_uas'         => $existingMk->pivot->nilai_uas,
                'nilai_akhir_angka' => $existingMk->pivot->nilai_akhir_angka,
            ];
        }

        $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->detach();
        
        $attachData = [];
        foreach ($mata_kuliah_ids as $mk_id) {
            $saved = $gradesToKeep[$mk_id] ?? [];
            $kelasDipilih = !empty($pilihanKelas[$mk_id]) ? strtoupper(trim($pilihanKelas[$mk_id])) : 'A';
            $attachData[$mk_id] = [
                'tahun_akademik_id' => $periodeAktif->id,
                'nama_kelas'        => $kelasDipilih,
                'nilai'             => $saved['nilai'] ?? null,
                'nilai_kehadiran'   => $saved['nilai_kehadiran'] ?? null,
                'nilai_tugas'       => $saved['nilai_tugas'] ?? null,
                'nilai_uts'         => $saved['nilai_uts'] ?? null,
                'nilai_uas'         => $saved['nilai_uas'] ?? null,
                'nilai_akhir_angka' => $saved['nilai_akhir_angka'] ?? null,
            ];
        }

        if (!empty($attachData)) {
            $mahasiswa->mataKuliahs()->attach($attachData);
            $mahasiswa->status_krs = 'Menunggu Persetujuan';
        } else {
            $mahasiswa->status_krs = 'Belum Mengajukan';
        }
        $mahasiswa->save();

        return redirect()->route('krs.index')->with('success', 'Kartu Rencana Studi (KRS) berhasil disimpan.');
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
            return redirect()->back()->with('error', 'KRS sudah divalidasi dan dikunci.');
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
        if (!$mahasiswa || $mahasiswa->status_mahasiswa !== 'Aktif') {
            return response()->json(['message' => 'Hanya mahasiswa Aktif yang dapat mengisi KRS.'], 403);
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

        $batas = $this->academicService->hitungBatasSks($mahasiswa);
        $allowedSemesters = in_array($periodeAktif->semester, ['Ganjil', 'Gasal']) ? [1, 3, 5, 7] : [2, 4, 6, 8];
        
        $mata_kuliahs = MataKuliah::with(['jadwals' => function ($q) {
            $q->select('mata_kuliah_id', 'nama_kelas', 'hari', 'jam_mulai', 'jam_selesai', 'ruang', 'kuota');
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
        if (!$mahasiswa || $mahasiswa->status_mahasiswa !== 'Aktif') {
            return response()->json(['message' => 'Hanya mahasiswa Aktif yang dapat mengisi KRS.'], 403);
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
        $batas = $this->academicService->hitungBatasSks($mahasiswa);

        $ruleErrors = $this->academicService->validateKrsRules($mahasiswa, $periodeAktif, $mata_kuliah_ids, $batas['max_sks']);
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
            $gradesToKeep[$existingMk->id] = [
                'nama_kelas'        => $existingMk->pivot->nama_kelas ?? 'A',
                'nilai'             => $existingMk->pivot->nilai,
                'nilai_kehadiran'   => $existingMk->pivot->nilai_kehadiran,
                'nilai_tugas'       => $existingMk->pivot->nilai_tugas,
                'nilai_uts'         => $existingMk->pivot->nilai_uts,
                'nilai_uas'         => $existingMk->pivot->nilai_uas,
                'nilai_akhir_angka' => $existingMk->pivot->nilai_akhir_angka,
            ];
        }

        $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->detach();
        
        $attachData = [];
        foreach ($mata_kuliah_ids as $mk_id) {
            $saved = $gradesToKeep[$mk_id] ?? [];
            $attachData[$mk_id] = [
                'tahun_akademik_id' => $periodeAktif->id,
                'nama_kelas'        => $saved['nama_kelas'] ?? 'A',
                'nilai'             => $saved['nilai'] ?? null,
                'nilai_kehadiran'   => $saved['nilai_kehadiran'] ?? null,
                'nilai_tugas'       => $saved['nilai_tugas'] ?? null,
                'nilai_uts'         => $saved['nilai_uts'] ?? null,
                'nilai_uas'         => $saved['nilai_uas'] ?? null,
                'nilai_akhir_angka' => $saved['nilai_akhir_angka'] ?? null,
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