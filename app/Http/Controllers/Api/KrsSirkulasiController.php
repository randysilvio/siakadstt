<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CekPeriodeKrsMiddleware;
use App\Http\Middleware\CekStatusPembayaranMiddleware;
use App\Models\Jadwal;
use App\Models\KrsPersetujuan;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\AcademicRuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Sirkulasi KRS untuk aplikasi seluler: ringkasan KRS mahasiswa (tetap terbaca di luar periode),
 * daftar & rincian KRS untuk Dosen Wali / Kaprodi, buka kembali persetujuan, dan hapus mata kuliah oleh Dosen Wali.
 * Aturan persetujuan memakai AcademicRuleService::terapkanValidasiKrs yang sama dengan web.
 */
class KrsSirkulasiController extends Controller
{
    public function __construct(private AcademicRuleService $aturan)
    {
    }

    private function periodeAktif(): ?TahunAkademik
    {
        return TahunAkademik::where('is_active', true)->first();
    }

    /** Mata kuliah KRS semester tertentu beserta jadwal rombel yang diambil. */
    private function rincianMataKuliah(Mahasiswa $mahasiswa, int $taId): Collection
    {
        $krs = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $taId)->with('dosen')->orderBy('semester')->orderBy('kode_mk')->get();
        $jadwal = Jadwal::whereIn('mata_kuliah_id', $krs->pluck('id'))->get()->groupBy('mata_kuliah_id');

        return $krs->map(function ($mk) use ($jadwal) {
            $kelas = strtoupper(trim((string) ($mk->pivot->nama_kelas ?? 'A')));
            return [
                'id'       => $mk->id,
                'kode_mk'  => $mk->kode_mk,
                'nama_mk'  => $mk->nama_mk,
                'sks'      => (int) $mk->sks,
                'semester' => (int) $mk->semester,
                'kelas'    => $kelas,
                'dosen'    => optional($mk->dosen)->nama_lengkap,
                'jadwal'   => ($jadwal[$mk->id] ?? collect())
                    ->filter(fn ($j) => strtoupper(trim((string) $j->nama_kelas)) === $kelas)
                    ->map(fn ($j) => ['hari' => $j->hari, 'jam_mulai' => substr((string) $j->jam_mulai, 0, 5), 'jam_selesai' => substr((string) $j->jam_selesai, 0, 5), 'ruang' => $j->ruang])
                    ->values(),
            ];
        })->values();
    }

    private function infoValidasi(?KrsPersetujuan $p): array
    {
        return [
            'catatan'         => optional($p)->catatan,
            'divalidasi_oleh' => $p && $p->divalidasi_oleh ? optional($p->validator)->name : null,
            'divalidasi_pada' => optional(optional($p)->divalidasi_pada)->toDateTimeString(),
        ];
    }

    /** Peran validator terhadap mahasiswa: 'wali', 'kaprodi', 'keduanya', 'admin', atau null bila tidak berwenang. */
    private function peranValidator(User $user, Mahasiswa $mahasiswa): ?string
    {
        $dosenId = optional($user->dosen)->id;
        $wali = $dosenId && (int) $mahasiswa->dosen_wali_id === (int) $dosenId;
        $kaprodi = $dosenId && ProgramStudi::where('id', $mahasiswa->program_studi_id)->where('kaprodi_dosen_id', $dosenId)->exists();

        return match (true) {
            $wali && $kaprodi => 'keduanya',
            $wali             => 'wali',
            $kaprodi          => 'kaprodi',
            $user->hasRole('admin') => 'admin',
            default           => null,
        };
    }

    // ------------------------------------------------------------------ mahasiswa

    /** GET /api/krs/saya — KRS semester aktif mahasiswa beserta alasan bila belum bisa diubah. */
    public function saya(Request $request): JsonResponse
    {
        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan.'], 404);
        }

        $periode = $this->periodeAktif();
        if (!$periode) {
            return response()->json(['message' => 'Belum ada Tahun Akademik yang aktif.'], 400);
        }

        $mk = $this->rincianMataKuliah($mahasiswa, $periode->id);
        $status = $mk->isEmpty() ? KrsPersetujuan::BELUM : $mahasiswa->statusKrsPada($periode->id);
        $persetujuan = $mahasiswa->persetujuanKrsPada($periode->id);
        $batas = $this->aturan->hitungBatasSks($mahasiswa);
        $keadaan = CekPeriodeKrsMiddleware::keadaan($periode);
        $gerbangBayar = CekStatusPembayaranMiddleware::alasanTertahan($mahasiswa);
        $edomBelum = $mahasiswa->status_evaluasi === 'belum_isi';

        $alasanTerkunci = match (true) {
            $mahasiswa->status_mahasiswa !== 'Aktif' => 'Status akademik Anda ' . strtoupper($mahasiswa->status_mahasiswa) . '; KRS hanya untuk mahasiswa Aktif.',
            $status === KrsPersetujuan::DISETUJUI    => 'KRS sudah disetujui dan dikunci.',
            $gerbangBayar !== null                    => $gerbangBayar,
            $edomBelum                                => 'Silakan isi evaluasi dosen (EDOM) terlebih dahulu.',
            !$keadaan['buka']                         => $keadaan['pesan'],
            default                                   => null,
        };

        return response()->json(array_merge([
            'semester'       => $periode->nama_semester,
            'periode'        => $keadaan,
            'status_krs'     => $status,
            'total_sks'      => (int) $mk->sum('sks'),
            'max_sks'        => $batas['max_sks'],
            'ipk'            => $batas['ipk'],
            'semester_ke'    => $batas['semester_ke'],
            'dosen_wali'     => optional($mahasiswa->dosenWali)->nama_lengkap,
            'bisa_ubah'      => $alasanTerkunci === null,
            'alasan_terkunci'=> $alasanTerkunci,
            'gerbang_bayar'  => $gerbangBayar,
            'edom_belum'     => $edomBelum,
            'bisa_cetak'     => $status === KrsPersetujuan::DISETUJUI && $mk->isNotEmpty(),
            'mata_kuliah'    => $mk,
        ], $this->infoValidasi($persetujuan)));
    }

    // ------------------------------------------------------------------ validator

    /**
     * GET /api/krs/mahasiswa?status=menunggu|disetujui|ditolak|belum|semua&q=
     * Mahasiswa aktif yang menjadi wewenang validator (anak wali dan/atau mahasiswa prodi yang dipimpin sebagai Kaprodi).
     */
    public function daftar(Request $request): JsonResponse
    {
        $user = $request->user();
        $periode = $this->periodeAktif();
        if (!$periode) {
            return response()->json(['ringkas' => [], 'data' => [], 'semester' => null]);
        }

        $query = Mahasiswa::where('status_mahasiswa', 'Aktif')->with(['programStudi', 'krsPersetujuans' => fn ($q) => $q->where('tahun_akademik_id', $periode->id)->with('validator')]);

        $dosenId = optional($user->dosen)->id;
        $prodiKaprodi = $dosenId ? ProgramStudi::where('kaprodi_dosen_id', $dosenId)->pluck('id')->all() : [];
        if (!$user->hasRole('admin')) {
            if (!$dosenId) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }
            $query->where(function ($q) use ($dosenId, $prodiKaprodi) {
                $q->where('dosen_wali_id', $dosenId);
                if ($prodiKaprodi) {
                    $q->orWhereIn('program_studi_id', $prodiKaprodi);
                }
            });
        }

        if ($request->filled('q')) {
            $cari = $request->input('q');
            $query->where(fn ($q) => $q->where('nama_lengkap', 'like', "%{$cari}%")->orWhere('nim', 'like', "%{$cari}%"));
        }

        $mahasiswas = $query->orderBy('nama_lengkap')->get();
        $sks = \DB::table('mahasiswa_mata_kuliah')
            ->join('mata_kuliahs', 'mata_kuliahs.id', '=', 'mahasiswa_mata_kuliah.mata_kuliah_id')
            ->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periode->id)
            ->whereIn('mahasiswa_mata_kuliah.mahasiswa_id', $mahasiswas->pluck('id'))
            ->groupBy('mahasiswa_mata_kuliah.mahasiswa_id')
            ->selectRaw('mahasiswa_mata_kuliah.mahasiswa_id as id, SUM(mata_kuliahs.sks) as sks, COUNT(*) as jumlah')
            ->get()->keyBy('id');

        $baris = $mahasiswas->map(function (Mahasiswa $m) use ($sks, $dosenId, $prodiKaprodi) {
            $p = $m->krsPersetujuans->first();
            $jumlah = (int) optional($sks[$m->id] ?? null)->jumlah;
            $status = $jumlah === 0 ? KrsPersetujuan::BELUM : ($p->status ?? KrsPersetujuan::BELUM);
            $wali = $dosenId && (int) $m->dosen_wali_id === (int) $dosenId;
            $kaprodi = in_array((int) $m->program_studi_id, array_map('intval', $prodiKaprodi), true);
            return [
                'id'              => $m->id,
                'nim'             => $m->nim,
                'nama_lengkap'    => $m->nama_lengkap,
                'prodi'           => optional($m->programStudi)->nama_prodi,
                'angkatan'        => $m->tahun_masuk,
                'status_krs'      => $status,
                'total_sks'       => (int) optional($sks[$m->id] ?? null)->sks,
                'jumlah_mk'       => $jumlah,
                'peran'           => $wali && $kaprodi ? 'keduanya' : ($wali ? 'wali' : ($kaprodi ? 'kaprodi' : 'admin')),
                'divalidasi_oleh' => $p && $p->divalidasi_oleh ? optional($p->validator)->name : null,
            ];
        });

        $peta = [
            'menunggu'  => KrsPersetujuan::MENUNGGU,
            'disetujui' => KrsPersetujuan::DISETUJUI,
            'ditolak'   => KrsPersetujuan::DITOLAK,
            'belum'     => KrsPersetujuan::BELUM,
        ];
        $ringkas = collect($peta)->map(fn ($label) => $baris->where('status_krs', $label)->count());
        $ringkas['semua'] = $baris->count();

        $filter = $request->input('status', 'menunggu');
        if (isset($peta[$filter])) {
            $baris = $baris->where('status_krs', $peta[$filter]);
        }

        return response()->json([
            'semester' => $periode->nama_semester,
            'ringkas'  => $ringkas,
            'data'     => $baris->values(),
        ]);
    }

    /** GET /api/krs/validasi/{id} — rincian KRS untuk ditinjau validator. */
    public function rincian(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $mahasiswa = Mahasiswa::with(['programStudi', 'dosenWali'])->findOrFail($id);
        $peran = $this->peranValidator($user, $mahasiswa);
        if (!$peran) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki wewenang untuk melihat KRS mahasiswa ini.'], 403);
        }

        $periode = $this->periodeAktif();
        if (!$periode) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada periode akademik yang aktif.'], 400);
        }

        $mk = $this->rincianMataKuliah($mahasiswa, $periode->id);
        $status = $mk->isEmpty() ? KrsPersetujuan::BELUM : $mahasiswa->statusKrsPada($periode->id);
        $persetujuan = $mahasiswa->persetujuanKrsPada($periode->id);
        $batas = $this->aturan->hitungBatasSks($mahasiswa);
        $disetujuiOrangLain = $status === KrsPersetujuan::DISETUJUI && $persetujuan
            && (int) $persetujuan->divalidasi_oleh !== (int) $user->id && !$user->hasRole('admin');

        return response()->json(array_merge([
            'mahasiswa' => [
                'id' => $mahasiswa->id, 'nim' => $mahasiswa->nim, 'nama_lengkap' => $mahasiswa->nama_lengkap,
                'prodi' => optional($mahasiswa->programStudi)->nama_prodi, 'angkatan' => $mahasiswa->tahun_masuk,
                'dosen_wali' => optional($mahasiswa->dosenWali)->nama_lengkap,
            ],
            'peran'        => $peran,
            'semester'     => $periode->nama_semester,
            'semester_ke'  => $batas['semester_ke'],
            'ipk'          => $batas['ipk'],
            'sks_lulus'    => $mahasiswa->totalSksLulus(),
            'max_sks'      => $batas['max_sks'],
            'total_sks'    => (int) $mk->sum('sks'),
            'status_krs'   => $status,
            'bisa_validasi'     => $mk->isNotEmpty() && !$disetujuiOrangLain,
            'bisa_buka_kembali' => $status === KrsPersetujuan::DISETUJUI && !$disetujuiOrangLain,
            'bisa_hapus_mk'     => in_array($peran, ['wali', 'keduanya'], true) && $status !== KrsPersetujuan::DISETUJUI && $mk->isNotEmpty(),
            'mata_kuliah'  => $mk,
        ], $this->infoValidasi($persetujuan)));
    }

    /** POST /api/krs/validasi/{id} — status: Setujui | Tolak | Buka (kembalikan ke Menunggu Persetujuan). */
    public function validasi(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'status'  => 'required|in:Setujui,Tolak,Buka',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $mahasiswa = Mahasiswa::findOrFail($id);
        if (!$this->peranValidator($user, $mahasiswa)) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki wewenang untuk memvalidasi KRS mahasiswa ini.'], 403);
        }

        $periode = $this->periodeAktif();
        if (!$periode) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada periode akademik yang aktif.'], 400);
        }

        $statusBaru = match ($request->status) {
            'Setujui' => KrsPersetujuan::DISETUJUI,
            'Tolak'   => KrsPersetujuan::DITOLAK,
            default   => KrsPersetujuan::MENUNGGU,
        };

        if ($statusBaru === KrsPersetujuan::DITOLAK && trim((string) $request->input('catatan')) === '') {
            return response()->json(['status' => 'error', 'message' => 'Tuliskan catatan alasan penolakan agar mahasiswa tahu apa yang harus diperbaiki.'], 422);
        }
        if ($statusBaru === KrsPersetujuan::MENUNGGU && $mahasiswa->statusKrsPada($periode->id) !== KrsPersetujuan::DISETUJUI) {
            return response()->json(['status' => 'error', 'message' => 'Hanya KRS yang sudah disetujui yang dapat dibuka kembali.'], 422);
        }

        $error = $this->aturan->terapkanValidasiKrs($mahasiswa, $periode, $statusBaru, $request->input('catatan'), $user);
        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 422);
        }

        $pesan = match ($statusBaru) {
            KrsPersetujuan::DISETUJUI => 'KRS ' . $mahasiswa->nama_lengkap . ' disetujui.',
            KrsPersetujuan::DITOLAK   => 'KRS ' . $mahasiswa->nama_lengkap . ' ditolak; mahasiswa dapat merevisi.',
            default                   => 'Persetujuan KRS dibuka kembali (Menunggu Persetujuan).',
        };

        return response()->json(['status' => 'success', 'message' => $pesan, 'status_krs' => $statusBaru]);
    }

    /** DELETE /api/krs/validasi/{id}/mk/{mk} — Dosen Wali menghapus satu mata kuliah dari KRS yang belum disetujui. */
    public function hapusMataKuliah(Request $request, int $id, int $mk): JsonResponse
    {
        $user = $request->user();
        $mahasiswa = Mahasiswa::findOrFail($id);
        if (!in_array($this->peranValidator($user, $mahasiswa), ['wali', 'keduanya'], true)) {
            return response()->json(['status' => 'error', 'message' => 'Hanya Dosen Wali mahasiswa ini yang dapat menghapus mata kuliah dari KRS-nya.'], 403);
        }

        $periode = $this->periodeAktif();
        if (!$periode) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada periode akademik yang aktif.'], 400);
        }
        if ($mahasiswa->krsDisetujuiPada($periode->id)) {
            return response()->json(['status' => 'error', 'message' => 'KRS sudah disetujui. Buka kembali persetujuan terlebih dahulu sebelum mengubah mata kuliah.'], 422);
        }

        $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periode->id)->detach($mk);
        if (!$mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periode->id)->exists()) {
            $mahasiswa->setStatusKrs($periode->id, KrsPersetujuan::BELUM);
        }

        return response()->json(['status' => 'success', 'message' => 'Mata kuliah dihapus dari KRS mahasiswa.']);
    }
}
