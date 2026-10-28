<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Models\VerumKehadiran;
use App\Models\VerumKelas;
use App\Models\VerumMateri;
use App\Models\VerumPengumpulan;
use App\Models\VerumPresensi;
use App\Models\VerumTugas;
use App\Support\BerkasPrivat;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Aturan kelas daring Verum yang dipakai bersama oleh halaman web dan API aplikasi seluler.
 * Hak akses: VerumKelasPolicy (kelola = dosen pembuat/pengampu, ikuti = mahasiswa peserta resmi).
 */
class VerumService
{
    public const ATURAN_KELAS = [
        'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
        'nama_kelas'     => 'required|string|max:255',
        'rombel'         => 'nullable|string|max:30',
        'deskripsi'      => 'nullable|string',
    ];

    public const ATURAN_MATERI = [
        'judul'       => 'required|string|max:150',
        'deskripsi'   => 'nullable|string|max:1000',
        'file_materi' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip|max:10240',
        'link_url'    => 'nullable|url|active_url',
    ];

    public const PESAN_MATERI = [
        'file_materi.max'   => 'Ukuran file tidak boleh lebih dari 10MB.',
        'file_materi.mimes' => 'Format file harus dokumen (PDF, Office) atau ZIP.',
    ];

    public const ATURAN_TUGAS = [
        'judul'         => 'required|string|max:255',
        'instruksi'     => 'required|string',
        'tenggat_waktu' => 'required|date',
    ];

    public const ATURAN_NILAI = [
        'nilai'           => 'nullable|array',
        'nilai.*'         => 'nullable|numeric|min:0|max:100',
        'catatan_dosen'   => 'nullable|array',
        'catatan_dosen.*' => 'nullable|string|max:500',
    ];

    public const ATURAN_JAWABAN = [
        'file_jawaban' => 'required|file|mimes:pdf,doc,docx,zip|max:10240',
    ];

    public const PESAN_JAWABAN = [
        'file_jawaban.required' => 'Anda harus memilih file untuk diunggah.',
        'file_jawaban.mimes'    => 'Format file harus PDF, DOC, DOCX, atau ZIP.',
        'file_jawaban.max'      => 'Ukuran file maksimal adalah 10MB.',
    ];

    public const ATURAN_PRESENSI = [
        'judul_pertemuan'     => 'required|string|max:255',
        'materi_aktual'       => 'nullable|string|max:1500',
        'metode_pembelajaran' => 'nullable|string|max:100',
        'pertemuan_ke'        => 'required|integer|min:1|max:32',
        'durasi'              => 'required|integer|min:1',
    ];

    public const ATURAN_JURNAL = [
        'judul_pertemuan'     => 'required|string|max:255',
        'materi_aktual'       => 'nullable|string|max:1500',
        'metode_pembelajaran' => 'nullable|string|max:100',
    ];

    public const METODE_BAWAAN = 'Tatap Muka / Ceramah & Diskusi';

    private function idMkTeamTeaching(int $dosenId): array
    {
        return DB::table('dosen_mata_kuliah')->where('dosen_id', $dosenId)->pluck('mata_kuliah_id')->toArray();
    }

    /** Kelas Verum semester aktif yang dapat dibuka pengguna (dosen: yang dikelola; mahasiswa: sesuai KRS disetujui & rombel). */
    public function kelasUntuk(User $user, ?TahunAkademik $ta): Collection
    {
        if (!$ta) {
            return collect();
        }

        if ($user->hasRole('dosen') && $user->dosen) {
            $pivot = $this->idMkTeamTeaching($user->dosen->id);
            return VerumKelas::where('tahun_akademik_id', $ta->id)
                ->where(fn ($q) => $q->where('dosen_id', $user->dosen->id)->orWhereIn('mata_kuliah_id', $pivot))
                ->with(['mataKuliah', 'dosen'])
                ->get();
        }

        if ($user->hasRole('mahasiswa') && $user->mahasiswa) {
            if (!$user->mahasiswa->krsDisetujuiPada($ta->id)) {
                return collect();
            }
            $rombelPerMk = $user->mahasiswa->mataKuliahs()
                ->wherePivot('tahun_akademik_id', $ta->id)
                ->get()
                ->mapWithKeys(fn ($mk) => [$mk->id => strtoupper(trim((string) ($mk->pivot->nama_kelas ?? 'A')))]);

            return VerumKelas::whereIn('mata_kuliah_id', $rombelPerMk->keys())
                ->where('tahun_akademik_id', $ta->id)
                ->with(['mataKuliah', 'dosen.user'])
                ->get()
                ->filter(fn ($k) => $k->rombel === null || trim($k->rombel) === ''
                    || strtoupper(trim($k->rombel)) === ($rombelPerMk[$k->mata_kuliah_id] ?? null))
                ->values();
        }

        return collect();
    }

    /** Mata kuliah yang diampu dosen (pengampu utama atau team teaching) beserta jadwal rombelnya. */
    public function mataKuliahDiampu(int $dosenId): Collection
    {
        $pivot = $this->idMkTeamTeaching($dosenId);
        return MataKuliah::with('jadwals')
            ->where(fn ($q) => $q->where('dosen_id', $dosenId)->orWhereIn('id', $pivot))
            ->orderBy('semester')
            ->get();
    }

    /** @return VerumKelas|string kelas baru, atau pesan kesalahan. */
    public function buatKelas(User $user, array $data): VerumKelas|string
    {
        $ta = TahunAkademik::where('is_active', true)->first();
        if (!$ta) {
            return 'Tidak ada tahun akademik aktif.';
        }
        $mk = MataKuliah::findOrFail($data['mata_kuliah_id']);
        if (!$user->can('ampu', $mk)) {
            return 'Anda tidak mengampu mata kuliah ini.';
        }

        $rombel = !empty($data['rombel']) ? strtoupper(trim($data['rombel'])) : null;
        if ($rombel !== null && !Jadwal::where('mata_kuliah_id', $mk->id)->whereRaw('UPPER(nama_kelas) = ?', [$rombel])->exists()) {
            return "Rombel {$rombel} tidak terdaftar pada jadwal mata kuliah ini.";
        }

        return VerumKelas::create([
            'mata_kuliah_id'    => $mk->id,
            'rombel'            => $rombel,
            'nama_kelas'        => strip_tags($data['nama_kelas']),
            'deskripsi'         => strip_tags((string) ($data['deskripsi'] ?? '')),
            'dosen_id'          => $user->dosen->id,
            'tahun_akademik_id' => $ta->id,
            'kode_kelas'        => Str::upper(Str::random(6)),
        ]);
    }

    /** @return VerumMateri|string */
    public function simpanMateri(VerumKelas $kelas, array $data, ?UploadedFile $file): VerumMateri|string
    {
        if (!$file && empty($data['link_url'])) {
            return 'Anda harus mengunggah file atau memasukkan link referensi.';
        }

        return $kelas->materi()->create([
            'judul'     => strip_tags($data['judul']),
            'deskripsi' => strip_tags((string) ($data['deskripsi'] ?? '')),
            'file_path' => $file ? $file->store('materi', 'public') : null,
            'link_url'  => $data['link_url'] ?? null,
        ]);
    }

    public function hapusMateri(VerumMateri $materi): void
    {
        if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
            Storage::disk('public')->delete($materi->file_path);
        }
        $materi->delete();
    }

    public function simpanTugas(VerumKelas $kelas, array $data): VerumTugas
    {
        return $kelas->tugas()->create([
            'judul'         => strip_tags($data['judul']),
            'instruksi'     => strip_tags($data['instruksi']),
            'tenggat_waktu' => $data['tenggat_waktu'],
        ]);
    }

    public function hapusTugas(VerumTugas $tugas): void
    {
        foreach ($tugas->pengumpulan as $kumpul) {
            BerkasPrivat::hapus($kumpul->file_path);
        }
        $tugas->delete();
    }

    /** Nilai (0–100) & catatan dosen; hanya untuk peserta resmi. Boleh tanpa unggahan (tugas luring). */
    public function simpanNilai(VerumTugas $tugas, array $nilai, array $catatan): void
    {
        $peserta = $tugas->kelas->pesertaQuery()->pluck('mahasiswas.id')->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($tugas, $nilai, $catatan, $peserta) {
            foreach ($nilai as $mhsId => $skor) {
                if (!in_array((int) $mhsId, $peserta, true)) {
                    continue;
                }
                $teks = isset($catatan[$mhsId]) ? strip_tags(trim((string) $catatan[$mhsId])) : null;
                $skorBersih = ($skor !== null && $skor !== '') ? round((float) $skor, 2) : null;

                $ada = VerumPengumpulan::where('tugas_id', $tugas->id)->where('mahasiswa_id', $mhsId)->first();
                if ($ada) {
                    $ada->update(['nilai' => $skorBersih, 'catatan_dosen' => $teks]);
                } elseif ($skorBersih !== null || !empty($teks)) {
                    VerumPengumpulan::create([
                        'tugas_id'          => $tugas->id,
                        'mahasiswa_id'      => $mhsId,
                        'file_path'         => '',
                        'waktu_pengumpulan' => now(),
                        'nilai'             => $skorBersih,
                        'catatan_dosen'     => $teks,
                    ]);
                }
            }
        });
    }

    /** @return string|null pesan kesalahan, atau null bila berhasil. */
    public function kumpulkan(VerumTugas $tugas, Mahasiswa $mahasiswa, UploadedFile $file): ?string
    {
        if (!$tugas->kelas || !$tugas->kelas->isPeserta($mahasiswa)) {
            return 'Anda tidak terdaftar sebagai peserta kelas/rombel ini.';
        }
        if (now()->isAfter($tugas->tenggat_waktu)) {
            return 'Gagal. Batas waktu pengumpulan tugas sudah ditutup.';
        }

        $ada = VerumPengumpulan::where('tugas_id', $tugas->id)->where('mahasiswa_id', $mahasiswa->id)->first();
        $path = BerkasPrivat::simpan($file, 'jawaban_tugas');
        if ($ada) {
            BerkasPrivat::hapus($ada->file_path);
        }

        VerumPengumpulan::updateOrCreate(
            ['tugas_id' => $tugas->id, 'mahasiswa_id' => $mahasiswa->id],
            ['file_path' => $path, 'waktu_pengumpulan' => now()]
        );

        return null;
    }

    public function bukaPresensi(VerumKelas $kelas, array $data): VerumPresensi
    {
        return $kelas->presensi()->create([
            'judul_pertemuan'     => strip_tags($data['judul_pertemuan']),
            'materi_aktual'       => !empty($data['materi_aktual']) ? strip_tags($data['materi_aktual']) : null,
            'metode_pembelajaran' => !empty($data['metode_pembelajaran']) ? strip_tags($data['metode_pembelajaran']) : self::METODE_BAWAAN,
            'pertemuan_ke'        => (int) $data['pertemuan_ke'],
            'waktu_buka'          => now(),
            'waktu_tutup'         => now()->addMinutes((int) $data['durasi']),
        ]);
    }

    public function perbaruiJurnal(VerumPresensi $presensi, array $data): void
    {
        $presensi->update([
            'judul_pertemuan'     => strip_tags($data['judul_pertemuan']),
            'materi_aktual'       => !empty($data['materi_aktual']) ? strip_tags($data['materi_aktual']) : null,
            'metode_pembelajaran' => !empty($data['metode_pembelajaran']) ? strip_tags($data['metode_pembelajaran']) : self::METODE_BAWAAN,
        ]);
    }

    /** @return array{0: string, 1: string} [jenis: success|warning|error, pesan] */
    public function hadir(VerumPresensi $presensi, Mahasiswa $mahasiswa): array
    {
        if (!$presensi->kelas || !$presensi->kelas->isPeserta($mahasiswa)) {
            return ['error', 'Anda tidak terdaftar sebagai peserta kelas/rombel ini.'];
        }
        if (now()->isBefore($presensi->waktu_buka)) {
            return ['error', 'Sesi presensi belum dibuka.'];
        }
        if (now()->isAfter($presensi->waktu_tutup)) {
            return ['error', 'Sesi presensi sudah ditutup.'];
        }
        if (VerumKehadiran::where('presensi_id', $presensi->id)->where('mahasiswa_id', $mahasiswa->id)->exists()) {
            return ['warning', 'Anda sudah melakukan presensi untuk sesi ini.'];
        }

        VerumKehadiran::create([
            'presensi_id'  => $presensi->id,
            'mahasiswa_id' => $mahasiswa->id,
            'status'       => 'hadir',
            'waktu_absen'  => now(),
        ]);

        return ['success', 'Kehadiran Anda berhasil dicatat.'];
    }

    /** Tautan ruang vicon Jitsi kelas (sama dengan halaman web). */
    public static function urlVicon(VerumKelas $kelas): string
    {
        return 'https://meet.jit.si/STTGPI-Verum-' . str_replace(' ', '', (string) $kelas->kode_kelas);
    }
}
