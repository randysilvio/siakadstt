<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Models\KrsPersetujuan;
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

    /**
     * Mengambil daftar Mata Kuliah yang ditawarkan: kurikulum aktif prodi mahasiswa pada semester aktif.
     */
    private function getOfferedMataKuliahs(Mahasiswa $mahasiswa, TahunAkademik $periodeAktif)
    {
        return $this->academicService->mataKuliahDitawarkanQuery($mahasiswa, $periodeAktif)
            ->with(['prasyarats', 'jadwals'])
            ->orderBy('semester')
            ->orderBy('kode_mk')
            ->get();
    }

    /**
     * Status KRS semester aktif; status yang tertinggal tanpa KRS dikembalikan ke "Belum Mengajukan".
     */
    private function statusSemesterIni(Mahasiswa $mahasiswa, TahunAkademik $periodeAktif, bool $punyaKrs): string
    {
        $status = $mahasiswa->statusKrsPada($periodeAktif->id);
        if (!$punyaKrs && $status !== KrsPersetujuan::BELUM) {
            $mahasiswa->setStatusKrs($periodeAktif->id, KrsPersetujuan::BELUM);
            $status = KrsPersetujuan::BELUM;
        } elseif ($mahasiswa->status_krs !== $status) {
            // Selaraskan salinan kolom status_krs dengan status semester aktif
            $mahasiswa->forceFill(['status_krs' => $status])->save();
        }
        return $status;
    }

    /**
     * Mengganti isi KRS semester aktif secara atomik (transaksi) dan mempertahankan nilai yang sudah ada.
     *
     * @return array<int,string> daftar pesan kesalahan aturan akademik (kosong bila berhasil)
     */
    private function simpanKrs(Mahasiswa $mahasiswa, TahunAkademik $periodeAktif, array $mataKuliahIds, array $pilihanKelas): array
    {
        $batas = $this->academicService->hitungBatasSks($mahasiswa);

        // attempts = 3: bila dua mahasiswa merebut kursi terakhir bersamaan dan MySQL mendeteksi deadlock,
        // transaksi diulang otomatis (lalu ditolak rapi karena kuota penuh) alih-alih error 500.
        return DB::transaction(function () use ($mahasiswa, $periodeAktif, $mataKuliahIds, $pilihanKelas, $batas) {
            $ruleErrors = $this->academicService->validateKrsRules($mahasiswa, $periodeAktif, $mataKuliahIds, $batas['max_sks'], $pilihanKelas);
            if (!empty($ruleErrors)) {
                return $ruleErrors;
            }

            $existingKrs = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->get();
            $gradesToKeep = [];
            foreach ($existingKrs as $existingMk) {
                $gradesToKeep[$existingMk->id] = [
                    'nama_kelas'        => $existingMk->pivot->nama_kelas ?? 'A',
                    'nilai'             => $existingMk->pivot->nilai,
                    'nilai_manual'      => (bool) $existingMk->pivot->nilai_manual,
                    'nilai_kehadiran'   => $existingMk->pivot->nilai_kehadiran,
                    'nilai_tugas'       => $existingMk->pivot->nilai_tugas,
                    'nilai_uts'         => $existingMk->pivot->nilai_uts,
                    'nilai_uas'         => $existingMk->pivot->nilai_uas,
                    'nilai_akhir_angka' => $existingMk->pivot->nilai_akhir_angka,
                ];
            }

            $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->detach();

            $attachData = [];
            foreach ($mataKuliahIds as $mkId) {
                $saved = $gradesToKeep[$mkId] ?? [];
                $kelasDipilih = !empty($pilihanKelas[$mkId]) ? strtoupper(trim($pilihanKelas[$mkId])) : ($saved['nama_kelas'] ?? 'A');
                $attachData[$mkId] = [
                    'tahun_akademik_id' => $periodeAktif->id,
                    'nama_kelas'        => $kelasDipilih,
                    'nilai'             => $saved['nilai'] ?? null,
                    'nilai_manual'      => $saved['nilai_manual'] ?? false,
                    'nilai_kehadiran'   => $saved['nilai_kehadiran'] ?? null,
                    'nilai_tugas'       => $saved['nilai_tugas'] ?? null,
                    'nilai_uts'         => $saved['nilai_uts'] ?? null,
                    'nilai_uas'         => $saved['nilai_uas'] ?? null,
                    'nilai_akhir_angka' => $saved['nilai_akhir_angka'] ?? null,
                ];
            }

            if (!empty($attachData)) {
                $mahasiswa->mataKuliahs()->attach($attachData);
                $mahasiswa->setStatusKrs($periodeAktif->id, KrsPersetujuan::MENUNGGU);
            } else {
                $mahasiswa->setStatusKrs($periodeAktif->id, KrsPersetujuan::BELUM);
            }

            return [];
        }, 3);
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
        $statusKrs = $this->statusSemesterIni($mahasiswa, $periodeAktif, $krsSemesterIni);

        $isLocked = ($statusKrs === KrsPersetujuan::DISETUJUI && $krsSemesterIni);

        $batas = $this->academicService->hitungBatasSks($mahasiswa);
        $ipk = $batas['ipk'];
        $max_sks = $batas['max_sks'];

        $mk_lulus_ids = $mahasiswa->mataKuliahs()
            ->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])
            ->pluck('mata_kuliahs.id')
            ->toArray();

        $mata_kuliahs = $this->getOfferedMataKuliahs($mahasiswa, $periodeAktif);

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
            'semester_ke'       => $batas['semester_ke'],
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

        if ($mahasiswa->krsDisetujuiPada($periodeAktif->id)
            && $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists()) {
            return redirect()->route('krs.index')->with('error', 'KRS Anda sudah final dan disetujui.');
        }

        $request->validate([
            'mata_kuliahs'   => 'nullable|array',
            'mata_kuliahs.*' => 'integer|exists:mata_kuliahs,id',
            'nama_kelas'     => 'nullable|array',
            'nama_kelas.*'   => 'nullable|string|max:30',
        ]);

        $mataKuliahIds = array_values(array_unique(array_map('intval', $request->input('mata_kuliahs', []))));
        $ruleErrors = $this->simpanKrs($mahasiswa, $periodeAktif, $mataKuliahIds, $request->input('nama_kelas', []));

        if (!empty($ruleErrors)) {
            throw ValidationException::withMessages(['mata_kuliahs' => $ruleErrors]);
        }

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
        if ($mahasiswa->krsDisetujuiPada($periodeAktif->id) && $krsSemesterIni) {
            return redirect()->back()->with('error', 'KRS sudah divalidasi dan dikunci.');
        }

        $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->detach($id);

        if (!$mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists()) {
            $mahasiswa->setStatusKrs($periodeAktif->id, KrsPersetujuan::BELUM);
        }

        return redirect()->back()->with('success', 'Mata kuliah berhasil dihapus dari KRS.');
    }

    public function getPerluValidasiApi(Request $request)
    {
        $user = $request->user();
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        if (!$periodeAktif) {
            return response()->json([]);
        }

        $query = Mahasiswa::where('status_mahasiswa', 'Aktif')
            ->whereHas('krsPersetujuans', function ($q) use ($periodeAktif) {
                $q->where('tahun_akademik_id', $periodeAktif->id)->where('status', KrsPersetujuan::MENUNGGU);
            });

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
                    'name'         => $mhs->nama_lengkap,
                    'nama_lengkap' => $mhs->nama_lengkap,
                    'nim'          => $mhs->nim,
                ]
            ];
        });

        return response()->json($formattedData);
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
        $statusKrs = $this->statusSemesterIni($mahasiswa, $periodeAktif, $krsSemesterIni);

        $batas = $this->academicService->hitungBatasSks($mahasiswa);
        $mata_kuliahs = $this->getOfferedMataKuliahs($mahasiswa, $periodeAktif);

        $krsSekarang = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $periodeAktif->id)
            ->get();
        $mk_diambil_ids = $krsSekarang->pluck('id')->toArray();

        // Kursi terisi per mata kuliah & rombel (untuk menampilkan sisa kuota di aplikasi)
        $terisi = DB::table('mahasiswa_mata_kuliah')
            ->select('mata_kuliah_id', 'nama_kelas', DB::raw('COUNT(*) as total'))
            ->where('tahun_akademik_id', $periodeAktif->id)
            ->groupBy('mata_kuliah_id', 'nama_kelas')
            ->get()
            ->groupBy('mata_kuliah_id')
            ->map(fn ($baris) => $baris->mapWithKeys(fn ($b) => [strtoupper($b->nama_kelas ?? 'A') => (int) $b->total]));

        // Untuk filter "sembunyikan yang sudah lulus" & tanda prasyarat terpenuhi di aplikasi
        $mk_lulus_ids = $mahasiswa->mataKuliahs()
            ->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])
            ->pluck('mata_kuliahs.id')->unique()->values();
        $mata_kuliahs->loadMissing('dosen:id,nama_lengkap');

        return response()->json([
            'semester'       => $periodeAktif->nama_semester,
            'mk_lulus_ids'   => $mk_lulus_ids,
            'kelas_diambil'  => $krsSekarang->mapWithKeys(fn ($mk) => [$mk->id => strtoupper($mk->pivot->nama_kelas ?? 'A')]),
            'terisi'         => $terisi,
            'catatan'        => optional($mahasiswa->persetujuanKrsPada($periodeAktif->id))->catatan,
            'ipk'            => $batas['ipk'],
            'max_sks'        => $batas['max_sks'],
            'semester_ke'    => $batas['semester_ke'],
            'mk_diambil_ids' => $mk_diambil_ids,
            'mata_kuliahs'   => $mata_kuliahs,
            'status_krs'     => $statusKrs
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
        if ($mahasiswa->krsDisetujuiPada($periodeAktif->id) && $krsSemesterIni) {
            return response()->json(['message' => 'KRS sudah disetujui, tidak bisa diubah.'], 403);
        }

        $request->validate([
            'mata_kuliahs'   => 'nullable|array',
            'mata_kuliahs.*' => 'integer|exists:mata_kuliahs,id',
            'nama_kelas'     => 'nullable|array',
            'nama_kelas.*'   => 'nullable|string|max:30',
        ]);

        $mataKuliahIds = array_values(array_unique(array_map('intval', $request->input('mata_kuliahs', []))));
        $ruleErrors = $this->simpanKrs($mahasiswa, $periodeAktif, $mataKuliahIds, $request->input('nama_kelas', []));

        if (!empty($ruleErrors)) {
            return response()->json([
                'status'  => 'error',
                'message' => implode(' ', $ruleErrors),
                'errors'  => ['mata_kuliahs' => $ruleErrors]
            ], 422);
        }

        return response()->json(['message' => 'KRS Berhasil Disimpan']);
    }
}
