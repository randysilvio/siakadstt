<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Mahasiswa;
use Illuminate\Support\Collection;

/**
 * Jadwal kuliah: urutan hari & jam, serta jadwal pribadi mahasiswa sesuai rombel KRS-nya.
 */
class JadwalService
{
    public const URUTAN_HARI = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];

    /** Urutkan per hari lalu per jam mulai. */
    public function urutkan(Collection $jadwals): Collection
    {
        return $jadwals->sortBy(fn ($j) => sprintf('%02d-%s', self::URUTAN_HARI[$j->hari] ?? 99, $j->jam_mulai))->values();
    }

    /**
     * Jadwal mahasiswa pada satu tahun akademik: hanya slot rombel (nama_kelas) yang diambil di KRS.
     */
    public function jadwalMahasiswa(Mahasiswa $mahasiswa, int $tahunAkademikId, array $with = ['mataKuliah.dosen']): Collection
    {
        $rombel = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $tahunAkademikId)
            ->get()
            ->mapWithKeys(fn ($mk) => [$mk->id => strtoupper($mk->pivot->nama_kelas ?? 'A')]);

        if ($rombel->isEmpty()) {
            return collect();
        }

        $jadwals = Jadwal::whereIn('mata_kuliah_id', $rombel->keys())
            ->with($with)
            ->get()
            ->filter(fn ($j) => strtoupper($j->nama_kelas ?? 'A') === ($rombel[$j->mata_kuliah_id] ?? 'A'));

        return $this->urutkan($jadwals);
    }
}
