<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Models\VerumKelas;
use App\Models\VerumMateri;
use App\Models\VerumPengumpulan;
use App\Models\VerumPostingan;
use App\Models\VerumPresensi;
use App\Models\VerumTugas;
use App\Services\VerumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Verum (kelas daring) untuk aplikasi seluler. Aturan & hak akses identik dengan web (VerumService + VerumKelasPolicy).
 */
class VerumController extends Controller
{
    public function __construct(private VerumService $verum)
    {
    }

    private function tolak(string $pesan, int $kode = 403): JsonResponse
    {
        return response()->json(['message' => $pesan], $kode);
    }

    private function bolehKelola(User $user, ?VerumKelas $kelas): bool
    {
        return $kelas && $user->can('kelola', $kelas);
    }

    private function ringkasKelas(VerumKelas $k): array
    {
        return [
            'id'                => $k->id,
            'nama_kelas'        => $k->nama_kelas,
            'deskripsi'         => $k->deskripsi,
            'rombel'            => $k->rombel,
            'kode_kelas'        => $k->kode_kelas,
            'is_meeting_active' => (bool) $k->is_meeting_active,
            'mata_kuliah'       => $k->mataKuliah ? ['id' => $k->mataKuliah->id, 'kode_mk' => $k->mataKuliah->kode_mk, 'nama_mk' => $k->mataKuliah->nama_mk, 'sks' => (int) $k->mataKuliah->sks] : null,
            'dosen'             => optional($k->dosen)->nama_lengkap,
        ];
    }

    // ------------------------------------------------------------------ kelas

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $peran = ($user->hasRole('dosen') && $user->dosen) ? 'dosen' : (($user->hasRole('mahasiswa') && $user->mahasiswa) ? 'mahasiswa' : null);
        if (!$peran) {
            return $this->tolak('Verum hanya untuk dosen dan mahasiswa.');
        }

        $ta = TahunAkademik::where('is_active', true)->first();
        $kelas = $this->verum->kelasUntuk($user, $ta);
        if ($kelas->isNotEmpty()) {
            $kelas->load(['presensi' => fn ($q) => $q->where('waktu_buka', '<=', now())->where('waktu_tutup', '>=', now())]);
            $kelas->loadCount(['materi', 'tugas']);
        }

        $pesan = null;
        if (!$ta) {
            $pesan = 'Saat ini tidak ada Tahun Akademik yang aktif.';
        } elseif ($peran === 'mahasiswa' && !$user->mahasiswa->krsDisetujuiPada($ta->id)) {
            $pesan = 'Kelas Verum terbuka setelah KRS semester ini disetujui Dosen Wali / Kaprodi.';
        }

        return response()->json([
            'peran'    => $peran,
            'semester' => optional($ta)->nama_semester,
            'pesan'    => $pesan,
            'data'     => $kelas->map(fn ($k) => $this->ringkasKelas($k) + [
                'jumlah_materi'    => (int) $k->materi_count,
                'jumlah_tugas'     => (int) $k->tugas_count,
                'presensi_terbuka' => $k->presensi->isNotEmpty(),
            ])->values(),
        ]);
    }

    /** Pilihan mata kuliah & rombel untuk dosen yang akan membuat kelas. */
    public function formKelas(Request $request): JsonResponse
    {
        $dosen = $request->user()->dosen;
        if (!$dosen) {
            return $this->tolak('Hanya dosen yang dapat membuat kelas.');
        }

        return response()->json($this->verum->mataKuliahDiampu($dosen->id)->map(fn ($mk) => [
            'id'       => $mk->id,
            'kode_mk'  => $mk->kode_mk,
            'nama_mk'  => $mk->nama_mk,
            'semester' => (int) $mk->semester,
            'rombel'   => $mk->jadwals->pluck('nama_kelas')->map(fn ($r) => strtoupper(trim((string) $r)))->filter()->unique()->sort()->values(),
        ])->values());
    }

    public function simpanKelas(Request $request): JsonResponse
    {
        if (!$request->user()->dosen) {
            return $this->tolak('Hanya dosen yang dapat membuat kelas.');
        }
        $hasil = $this->verum->buatKelas($request->user(), $request->validate(VerumService::ATURAN_KELAS));
        if (is_string($hasil)) {
            return $this->tolak($hasil, 422);
        }

        return response()->json(['message' => 'Kelas Verum berhasil dibuat.', 'id' => $hasil->id]);
    }

    public function hapusKelas(Request $request, VerumKelas $kelas): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $kelas)) {
            return $this->tolak('Anda tidak berhak menghapus kelas ini.');
        }
        $kelas->delete();

        return response()->json(['message' => 'Kelas berhasil dihapus.']);
    }

    public function show(Request $request, VerumKelas $kelas): JsonResponse
    {
        $user = $request->user();
        $kelola = $this->bolehKelola($user, $kelas);
        $mahasiswa = $user->mahasiswa;
        if (!$kelola && !($mahasiswa && $kelas->isPeserta($mahasiswa))) {
            return $this->tolak('Anda tidak terdaftar di kelas ini atau KRS Anda belum disetujui.');
        }

        $kelas->load(['mataKuliah', 'dosen', 'materi', 'tugas', 'postingan.user.roles', 'presensi.kehadiran']);
        $pesertaIds = $kelas->pesertaQuery()->pluck('mahasiswas.id')->map(fn ($id) => (int) $id);
        $kumpulan = VerumPengumpulan::whereIn('tugas_id', $kelas->tugas->pluck('id'))->get()->groupBy('tugas_id');

        $tugas = $kelas->tugas->sortByDesc('tenggat_waktu')->map(function (VerumTugas $t) use ($kelola, $mahasiswa, $kumpulan, $pesertaIds) {
            $semua = ($kumpulan[$t->id] ?? collect());
            $baris = [
                'id'        => $t->id,
                'judul'     => $t->judul,
                'instruksi' => $t->instruksi,
                'tenggat'   => optional($t->tenggat_waktu)->toDateTimeString(),
                'lewat'     => $t->tenggat_waktu ? now()->isAfter($t->tenggat_waktu) : false,
            ];
            if ($kelola) {
                $baris['rekap'] = [
                    'peserta'     => $pesertaIds->count(),
                    'dikumpulkan' => $semua->filter(fn ($k) => !empty($k->file_path))->count(),
                    'dinilai'     => $semua->whereNotNull('nilai')->count(),
                ];
            } else {
                $milik = $semua->firstWhere('mahasiswa_id', $mahasiswa->id);
                $baris['saya'] = $this->ringkasPengumpulan($milik);
            }
            return $baris;
        })->values();

        $presensi = $kelas->presensi->sortByDesc('pertemuan_ke')->map(function (VerumPresensi $p) use ($kelola, $mahasiswa) {
            $baris = [
                'id'                  => $p->id,
                'pertemuan_ke'        => (int) $p->pertemuan_ke,
                'judul_pertemuan'     => $p->judul_pertemuan,
                'materi_aktual'       => $p->materi_aktual,
                'metode_pembelajaran' => $p->metode_pembelajaran,
                'waktu_buka'          => optional($p->waktu_buka)->toDateTimeString(),
                'waktu_tutup'         => optional($p->waktu_tutup)->toDateTimeString(),
                'terbuka'             => $p->waktu_buka && $p->waktu_tutup && now()->between($p->waktu_buka, $p->waktu_tutup),
            ];
            if ($kelola) {
                $baris['jumlah_hadir'] = $p->kehadiran->count();
            } else {
                $hadir = $p->kehadiran->firstWhere('mahasiswa_id', $mahasiswa->id);
                $baris['hadir'] = (bool) $hadir;
                $baris['waktu_hadir'] = $hadir ? (string) $hadir->waktu_absen : null;
            }
            return $baris;
        })->values();

        return response()->json([
            'kelas'     => $this->ringkasKelas($kelas),
            'peran'     => $kelola ? 'dosen' : 'mahasiswa',
            'peserta'   => $pesertaIds->count(),
            'vicon'     => ['aktif' => (bool) $kelas->is_meeting_active, 'url' => VerumService::urlVicon($kelas)],
            'pertemuan_berikut' => ((int) $kelas->presensi->max('pertemuan_ke')) + 1,
            'materi'    => $kelas->materi->sortByDesc('created_at')->map(fn (VerumMateri $m) => [
                'id'        => $m->id,
                'judul'     => $m->judul,
                'deskripsi' => $m->deskripsi,
                'file_url'  => $m->file_path ? asset('storage/' . $m->file_path) : null,
                'nama_file' => $m->file_path ? basename($m->file_path) : null,
                'link_url'  => $m->link_url,
                'dibuat'    => optional($m->created_at)->toDateTimeString(),
            ])->values(),
            'tugas'     => $tugas,
            'presensi'  => $presensi,
            'postingan' => $kelas->postingan->sortByDesc('created_at')->map(fn (VerumPostingan $p) => [
                'id'        => $p->id,
                'konten'    => $p->konten,
                'penulis'   => optional($p->user)->name ?? 'Pengguna',
                'dosen'     => $p->user ? $p->user->hasRole('dosen') : false,
                'waktu'     => optional($p->created_at)->toDateTimeString(),
                'bisa_hapus'=> $kelola || (int) $p->user_id === (int) $request->user()->id,
            ])->values(),
        ]);
    }

    private function ringkasPengumpulan(?VerumPengumpulan $k): array
    {
        return [
            'dikumpulkan' => $k && !empty($k->file_path),
            'waktu'       => $k ? optional($k->waktu_pengumpulan)->toDateTimeString() : null,
            'nilai'       => $k?->nilai,
            'catatan'     => $k?->catatan_dosen,
            'berkas_id'   => $k && !empty($k->file_path) ? $k->id : null,
        ];
    }

    public function vicon(Request $request, VerumKelas $kelas): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $kelas)) {
            return $this->tolak('Anda tidak memiliki akses.');
        }
        $aktif = $request->boolean('aktif');
        $kelas->update(['is_meeting_active' => $aktif]);

        return response()->json([
            'message' => $aktif ? 'Ruang kelas online telah dibuka.' : 'Kelas online telah diakhiri.',
            'vicon'   => ['aktif' => $aktif, 'url' => VerumService::urlVicon($kelas)],
        ]);
    }

    // ------------------------------------------------------------------ forum

    public function kirimPostingan(Request $request, VerumKelas $kelas): JsonResponse
    {
        $user = $request->user();
        if (!$this->bolehKelola($user, $kelas) && !($user->mahasiswa && $kelas->isPeserta($user->mahasiswa))) {
            return $this->tolak('Akses ditolak. Anda bukan anggota kelas ini.');
        }
        $request->validate(['konten' => 'required|string|max:2000']);
        VerumPostingan::create(['kelas_id' => $kelas->id, 'user_id' => $user->id, 'konten' => strip_tags($request->konten)]);

        return response()->json(['message' => 'Postingan berhasil ditambahkan.']);
    }

    public function hapusPostingan(Request $request, VerumPostingan $postingan): JsonResponse
    {
        $user = $request->user();
        if ((int) $postingan->user_id !== (int) $user->id && !$this->bolehKelola($user, $postingan->kelas)) {
            return $this->tolak('Anda tidak berhak menghapus postingan ini.');
        }
        $postingan->delete();

        return response()->json(['message' => 'Postingan berhasil dihapus.']);
    }

    // ------------------------------------------------------------------ materi

    public function simpanMateri(Request $request, VerumKelas $kelas): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $kelas)) {
            return $this->tolak('Aksi ini hanya untuk dosen pengampu kelas.');
        }
        $data = $request->validate(VerumService::ATURAN_MATERI, VerumService::PESAN_MATERI);
        $hasil = $this->verum->simpanMateri($kelas, $data, $request->file('file_materi'));
        if (is_string($hasil)) {
            return $this->tolak($hasil, 422);
        }

        return response()->json(['message' => 'Materi berhasil ditambahkan.']);
    }

    public function hapusMateri(Request $request, VerumMateri $materi): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $materi->kelas)) {
            return $this->tolak('Anda tidak berhak menghapus materi ini.');
        }
        $this->verum->hapusMateri($materi);

        return response()->json(['message' => 'Materi berhasil dihapus.']);
    }

    // ------------------------------------------------------------------ tugas

    public function simpanTugas(Request $request, VerumKelas $kelas): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $kelas)) {
            return $this->tolak('Aksi ini hanya untuk dosen pengampu kelas.');
        }
        $this->verum->simpanTugas($kelas, $request->validate(VerumService::ATURAN_TUGAS));

        return response()->json(['message' => 'Tugas berhasil dibuat.']);
    }

    public function hapusTugas(Request $request, VerumTugas $tugas): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $tugas->kelas)) {
            return $this->tolak('Anda tidak berhak menghapus tugas ini.');
        }
        $this->verum->hapusTugas($tugas);

        return response()->json(['message' => 'Tugas berhasil dihapus.']);
    }

    /** Rincian tugas: mahasiswa melihat pengumpulannya; dosen melihat rekap seluruh peserta. */
    public function tugas(Request $request, VerumTugas $tugas): JsonResponse
    {
        $user = $request->user();
        $kelas = $tugas->kelas;
        $kelola = $this->bolehKelola($user, $kelas);
        if (!$kelola && !($user->mahasiswa && $kelas && $kelas->isPeserta($user->mahasiswa))) {
            return $this->tolak('Anda tidak terdaftar di kelas ini.');
        }

        $dasar = [
            'id'        => $tugas->id,
            'kelas_id'  => $kelas->id,
            'kelas'     => $kelas->nama_kelas,
            'judul'     => $tugas->judul,
            'instruksi' => $tugas->instruksi,
            'tenggat'   => optional($tugas->tenggat_waktu)->toDateTimeString(),
            'lewat'     => $tugas->tenggat_waktu ? now()->isAfter($tugas->tenggat_waktu) : false,
            'peran'     => $kelola ? 'dosen' : 'mahasiswa',
        ];

        if (!$kelola) {
            $milik = VerumPengumpulan::where('tugas_id', $tugas->id)->where('mahasiswa_id', $user->mahasiswa->id)->first();
            return response()->json($dasar + ['saya' => $this->ringkasPengumpulan($milik)]);
        }

        $kumpulan = VerumPengumpulan::where('tugas_id', $tugas->id)->get()->keyBy('mahasiswa_id');
        $peserta = $kelas->pesertaQuery()->orderBy('nama_lengkap')->get(['mahasiswas.id', 'mahasiswas.nim', 'mahasiswas.nama_lengkap']);

        return response()->json($dasar + [
            'peserta' => $peserta->map(fn ($m) => [
                'id' => $m->id, 'nim' => $m->nim, 'nama_lengkap' => $m->nama_lengkap,
            ] + $this->ringkasPengumpulan($kumpulan[$m->id] ?? null))->values(),
        ]);
    }

    public function simpanNilai(Request $request, VerumTugas $tugas): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $tugas->kelas)) {
            return $this->tolak('Akses ditolak. Anda bukan pengampu kelas ini.');
        }
        $request->validate(VerumService::ATURAN_NILAI);
        $this->verum->simpanNilai($tugas, $request->input('nilai', []), $request->input('catatan_dosen', []));

        return response()->json(['message' => 'Nilai dan umpan balik tugas berhasil disimpan.']);
    }

    public function kumpulkan(Request $request, VerumTugas $tugas): JsonResponse
    {
        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa || !$tugas->kelas) {
            return $this->tolak('Aksi ini hanya untuk mahasiswa peserta kelas.');
        }
        $request->validate(VerumService::ATURAN_JAWABAN, VerumService::PESAN_JAWABAN);
        $error = $this->verum->kumpulkan($tugas, $mahasiswa, $request->file('file_jawaban'));
        if ($error) {
            return $this->tolak($error, 422);
        }

        return response()->json(['message' => 'Jawaban tugas berhasil dikumpulkan.']);
    }

    // ------------------------------------------------------------------ presensi & jurnal BAP

    public function bukaPresensi(Request $request, VerumKelas $kelas): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $kelas)) {
            return $this->tolak('Hanya dosen pengampu yang bisa membuka presensi.');
        }
        $p = $this->verum->bukaPresensi($kelas, $request->validate(VerumService::ATURAN_PRESENSI));

        return response()->json(['message' => 'Sesi presensi dan Jurnal BAP Pertemuan ke-' . $p->pertemuan_ke . ' berhasil dibuka.', 'id' => $p->id]);
    }

    public function perbaruiJurnal(Request $request, VerumPresensi $presensi): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $presensi->kelas)) {
            return $this->tolak('Hanya dosen pengampu yang dapat mengubah Jurnal BAP.');
        }
        $this->verum->perbaruiJurnal($presensi, $request->validate(VerumService::ATURAN_JURNAL));

        return response()->json(['message' => 'Jurnal BAP Pertemuan ke-' . $presensi->pertemuan_ke . ' berhasil diperbarui.']);
    }

    /** Daftar hadir satu pertemuan (dosen). */
    public function presensi(Request $request, VerumPresensi $presensi): JsonResponse
    {
        if (!$this->bolehKelola($request->user(), $presensi->kelas)) {
            return $this->tolak('Hanya dosen pengampu yang dapat melihat daftar hadir.');
        }
        $hadir = $presensi->kehadiran()->get()->keyBy('mahasiswa_id');
        $peserta = $presensi->kelas->pesertaQuery()->orderBy('nama_lengkap')->get(['mahasiswas.id', 'mahasiswas.nim', 'mahasiswas.nama_lengkap']);

        return response()->json([
            'id'              => $presensi->id,
            'pertemuan_ke'    => (int) $presensi->pertemuan_ke,
            'judul_pertemuan' => $presensi->judul_pertemuan,
            'materi_aktual'   => $presensi->materi_aktual,
            'metode_pembelajaran' => $presensi->metode_pembelajaran,
            'jumlah_hadir'    => $hadir->count(),
            'peserta'         => $peserta->map(fn ($m) => [
                'id' => $m->id, 'nim' => $m->nim, 'nama_lengkap' => $m->nama_lengkap,
                'hadir' => isset($hadir[$m->id]), 'waktu' => isset($hadir[$m->id]) ? (string) $hadir[$m->id]->waktu_absen : null,
            ])->values(),
        ]);
    }

    public function hadir(Request $request, VerumPresensi $presensi): JsonResponse
    {
        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa || !$presensi->kelas) {
            return $this->tolak('Aksi ini hanya untuk mahasiswa peserta kelas.');
        }
        [$jenis, $pesan] = $this->verum->hadir($presensi, $mahasiswa);

        return response()->json(['message' => $pesan, 'jenis' => $jenis], $jenis === 'error' ? 422 : 200);
    }
}
