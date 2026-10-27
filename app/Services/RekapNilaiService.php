<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Perhitungan IPK dan SKS lulus dalam satu query untuk banyak mahasiswa sekaligus.
 *
 * Aturan (sama dengan transkrip): untuk mata kuliah yang diulang, yang dipakai adalah nilai terbaik.
 * IPK = Σ(bobot × sks) / Σ sks dari mata kuliah bernilai; SKS lulus = Σ sks dengan nilai A–D.
 */
class RekapNilaiService
{
    public const BOBOT = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

    /**
     * @param  iterable<int>  $mahasiswaIds
     * @return array<int, array{ipk: float, sks_lulus: int, sks_bernilai: int}>
     */
    public function rekapBanyak(iterable $mahasiswaIds): array
    {
        $ids = collect($mahasiswaIds)->map(fn ($id) => (int) $id)->unique()->values();
        $hasil = $ids->mapWithKeys(fn ($id) => [$id => ['ipk' => 0.0, 'sks_lulus' => 0, 'sks_bernilai' => 0]])->all();

        if ($ids->isEmpty()) {
            return $hasil;
        }

        $baris = collect();
        foreach ($ids->chunk(1000) as $chunk) {
            $baris = $baris->concat(
                DB::table('mahasiswa_mata_kuliah as mmk')
                    ->join('mata_kuliahs as mk', 'mk.id', '=', 'mmk.mata_kuliah_id')
                    ->whereIn('mmk.mahasiswa_id', $chunk->all())
                    ->whereNotNull('mmk.nilai')
                    ->get(['mmk.mahasiswa_id', 'mmk.mata_kuliah_id', 'mmk.nilai', 'mk.sks'])
            );
        }

        foreach ($baris->groupBy('mahasiswa_id') as $mhsId => $nilaiMhs) {
            $hasil[(int) $mhsId] = $this->hitung($this->nilaiTerbaik($nilaiMhs));
        }

        return $hasil;
    }

    /** @return array{ipk: float, sks_lulus: int, sks_bernilai: int} */
    public function rekap(int $mahasiswaId): array
    {
        return $this->rekapBanyak([$mahasiswaId])[$mahasiswaId];
    }

    /**
     * Nilai terbaik per mata kuliah dari kumpulan baris (mata_kuliah_id, nilai, sks).
     *
     * @return Collection<int, object>
     */
    public function nilaiTerbaik(Collection $baris): Collection
    {
        return $baris->groupBy('mata_kuliah_id')->map(
            fn ($perMk) => $perMk->sortByDesc(fn ($r) => self::BOBOT[strtoupper((string) $r->nilai)] ?? -1)->first()
        );
    }

    /** @return array{ipk: float, sks_lulus: int, sks_bernilai: int} */
    private function hitung(Collection $terbaik): array
    {
        $sksBernilai = 0;
        $totalBobot = 0;
        $sksLulus = 0;

        foreach ($terbaik as $r) {
            $huruf = strtoupper((string) $r->nilai);
            $sks = (int) $r->sks;
            $sksBernilai += $sks;
            $totalBobot += (self::BOBOT[$huruf] ?? 0) * $sks;
            if (in_array($huruf, ['A', 'B', 'C', 'D'], true)) {
                $sksLulus += $sks;
            }
        }

        return [
            'ipk'          => $sksBernilai > 0 ? round($totalBobot / $sksBernilai, 2) : 0.0,
            'sks_lulus'    => $sksLulus,
            'sks_bernilai' => $sksBernilai,
        ];
    }

    /**
     * Data transkrip: mata kuliah bernilai terbaik (model MataKuliah dengan pivot), dikelompokkan per semester.
     *
     * @return array{matkul: Collection, krs_per_semester: Collection, total_sks: int, ipk: float}
     */
    public function transkrip(\App\Models\Mahasiswa $mahasiswa): array
    {
        $semua = $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->get();

        $terbaik = $semua->groupBy('id')->map(
            fn ($perMk) => $perMk->sortByDesc(fn ($mk) => self::BOBOT[strtoupper((string) $mk->pivot->nilai)] ?? -1)->first()
        )->values();

        return [
            'matkul'           => $terbaik,
            'krs_per_semester' => $terbaik->groupBy('semester'),
            'total_sks'        => (int) $terbaik->sum('sks'),
            'ipk'              => $mahasiswa->hitungIpk(),
        ];
    }
}
