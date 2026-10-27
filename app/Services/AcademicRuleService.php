<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Pengaturan;
use App\Models\TahunAkademik;
use App\Models\VerumKelas;
use App\Models\VerumPengumpulan;
use Illuminate\Support\Facades\DB;

class AcademicRuleService
{
    /**
     * Menghitung IPK dan batas maksimal SKS yang dapat diambil mahasiswa pada semester berjalan.
     */
    public function hitungBatasSks(Mahasiswa $mahasiswa): array
    {
        $ipk = $mahasiswa->hitungIpk();

        $maxSks = 15;
        if ($ipk == 0.00 && $mahasiswa->mataKuliahs()->wherePivotNotNull('nilai')->doesntExist()) {
            $maxSks = 24;
        } elseif ($ipk >= 3.00) {
            $maxSks = 24;
        } elseif ($ipk >= 2.50) {
            $maxSks = 21;
        } elseif ($ipk >= 2.00) {
            $maxSks = 18;
        }

        return [
            'ipk'     => $ipk,
            'max_sks' => $maxSks,
        ];
    }

    /**
     * Memvalidasi aturan pengajuan KRS (Semester, Batas SKS, Prasyarat, Kuota Kursi Kelas Paralel, & Bentrok Jadwal Rombel).
     */
    public function validateKrsRules(Mahasiswa $mahasiswa, TahunAkademik $periodeAktif, array $mataKuliahIds, int $maxSks, array $pilihanKelas = []): array
    {
        if (empty($mataKuliahIds)) {
            return [];
        }

        $allowedSemesters = in_array($periodeAktif->semester, ['Ganjil', 'Gasal']) ? [1, 3, 5, 7] : [2, 4, 6, 8];
        $mkDipilih = MataKuliah::with(['prasyarats', 'jadwals'])->whereIn('id', $mataKuliahIds)->get();

        if ($mkDipilih->count() !== count(array_unique($mataKuliahIds))) {
            return ['Terdapat mata kuliah yang tidak valid di dalam pilihan KRS.'];
        }

        foreach ($mkDipilih as $mk) {
            if (!in_array((int) $mk->semester, $allowedSemesters)) {
                return ["Mata kuliah {$mk->nama_mk} (Semester {$mk->semester}) tidak ditawarkan pada semester {$periodeAktif->semester}."];
            }
        }

        $sksDiambil = $mkDipilih->sum('sks');
        if ($sksDiambil > $maxSks) {
            return ["Total SKS yang dipilih ({$sksDiambil} SKS) melebihi batas maksimal SKS Anda ({$maxSks} SKS)."];
        }

        $mkLulusIds = $mahasiswa->mataKuliahs()
            ->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])
            ->pluck('mata_kuliahs.id')
            ->toArray();

        $errorPrasyarat = [];
        foreach ($mkDipilih as $mk) {
            foreach ($mk->prasyarats as $prasyarat) {
                if (!in_array($prasyarat->id, $mkLulusIds)) {
                    $errorPrasyarat[] = "Syarat ambil {$mk->nama_mk}: Wajib lulus {$prasyarat->nama_mk}.";
                }
            }
        }

        if (!empty($errorPrasyarat)) {
            return $errorPrasyarat;
        }

        // Deteksi kuota kursi dan jadwal bentrok sesuai Rombel Kelas (nama_kelas) yang dipilih mahasiswa
        $jadwalTerpilih = [];
        foreach ($mkDipilih as $mk) {
            $klsPilihan = isset($pilihanKelas[$mk->id]) ? strtoupper(trim((string) $pilihanKelas[$mk->id])) : null;

            $slotDipilih = null;
            if ($klsPilihan) {
                $slotDipilih = $mk->jadwals->first(fn($j) => strtoupper($j->nama_kelas ?? 'A') === $klsPilihan);
            }
            if (!$slotDipilih) {
                $slotDipilih = $mk->jadwals->first();
            }

            if ($slotDipilih) {
                $namaKlsAktual = strtoupper($slotDipilih->nama_kelas ?? 'A');
                $kuotaMaks = (int) ($slotDipilih->kuota ?? 40);

                $terisiSaatIni = DB::table('mahasiswa_mata_kuliah')
                    ->where('mata_kuliah_id', $mk->id)
                    ->where('tahun_akademik_id', $periodeAktif->id)
                    ->where('nama_kelas', $namaKlsAktual)
                    ->where('mahasiswa_id', '!=', $mahasiswa->id)
                    ->count();

                if ($kuotaMaks > 0 && $terisiSaatIni >= $kuotaMaks) {
                    return ["Kapasitas Kelas {$namaKlsAktual} untuk mata kuliah {$mk->nama_mk} sudah penuh ({$terisiSaatIni}/{$kuotaMaks}). Silakan pilih kelas paralel lain."];
                }

                foreach ($jadwalTerpilih as $j) {
                    if (
                        $slotDipilih->hari === $j['hari'] &&
                        $slotDipilih->jam_mulai < $j['jam_selesai'] &&
                        $slotDipilih->jam_selesai > $j['jam_mulai']
                    ) {
                        return ["Jadwal bentrok pada hari {$slotDipilih->hari}: {$mk->nama_mk} (Kelas {$namaKlsAktual}) vs {$j['nama_mk']} (Kelas {$j['kelas']})."];
                    }
                }

                $jadwalTerpilih[] = [
                    'hari'        => $slotDipilih->hari,
                    'jam_mulai'   => $slotDipilih->jam_mulai,
                    'jam_selesai' => $slotDipilih->jam_selesai,
                    'mk_id'       => $mk->id,
                    'nama_mk'     => $mk->nama_mk,
                    'kelas'       => $namaKlsAktual,
                ];
            }
        }

        return [];
    }

    public function bobotPenilaian(): array
    {
        $hadir = (float) Pengaturan::get('bobot_hadir_default', 10);
        $tugas = (float) Pengaturan::get('bobot_tugas_default', 20);
        $uts   = (float) Pengaturan::get('bobot_uts_default', 30);
        $uas   = (float) Pengaturan::get('bobot_uas_default', 40);

        $total = $hadir + $tugas + $uts + $uas;
        if ($total <= 0 || abs($total - 100) > 0.01) {
            return ['hadir' => 0.10, 'tugas' => 0.20, 'uts' => 0.30, 'uas' => 0.40];
        }

        return [
            'hadir' => $hadir / 100,
            'tugas' => $tugas / 100,
            'uts'   => $uts / 100,
            'uas'   => $uas / 100,
        ];
    }

    public function hitungNilaiAkhir(float $hadir, float $tugas, float $uts, float $uas): float
    {
        $bobot = $this->bobotPenilaian();
        return round(
            ($hadir * $bobot['hadir']) +
            ($tugas * $bobot['tugas']) +
            ($uts * $bobot['uts']) +
            ($uas * $bobot['uas']),
            2
        );
    }

    public function konversiAngkaKeHuruf(float $angka): string
    {
        if ($angka >= 80) return 'A';
        if ($angka >= 70) return 'B';
        if ($angka >= 60) return 'C';
        if ($angka >= 50) return 'D';
        return 'E';
    }

    public function syncNilaiVerumKeKrs(MataKuliah $mataKuliah, TahunAkademik $tahunAkademik): int
    {
        $verumKelasIds = VerumKelas::where('mata_kuliah_id', $mataKuliah->id)
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->pluck('id');

        if ($verumKelasIds->isEmpty()) {
            return 0;
        }

        $presensiIds = DB::table('verum_presensi')
            ->whereIn('kelas_id', $verumKelasIds)
            ->pluck('id');
        $totalPertemuan = $presensiIds->count();

        $tugasIds = DB::table('verum_tugas')
            ->whereIn('kelas_id', $verumKelasIds)
            ->pluck('id');
        $totalTugas = $tugasIds->count();

        $mahasiswas = $mataKuliah->mahasiswas()
            ->wherePivot('tahun_akademik_id', $tahunAkademik->id)
            ->where('mahasiswas.status_krs', 'Disetujui')
            ->get();

        $updatedCount = 0;

        DB::transaction(function () use ($mahasiswas, $mataKuliah, $tahunAkademik, $presensiIds, $totalPertemuan, $tugasIds, $totalTugas, &$updatedCount) {
            foreach ($mahasiswas as $mhs) {
                $nilaiHadir = $mhs->pivot->nilai_kehadiran;
                if ($totalPertemuan > 0) {
                    $jmlHadir = DB::table('verum_kehadiran')
                        ->whereIn('presensi_id', $presensiIds)
                        ->where('mahasiswa_id', $mhs->id)
                        ->where('status', 'hadir')
                        ->count();
                    $nilaiHadir = round(($jmlHadir / $totalPertemuan) * 100, 2);
                }

                $nilaiTugas = $mhs->pivot->nilai_tugas;
                if ($totalTugas > 0) {
                    $sumNilaiTugas = VerumPengumpulan::whereIn('tugas_id', $tugasIds)
                        ->where('mahasiswa_id', $mhs->id)
                        ->whereNotNull('nilai')
                        ->sum('nilai');
                    $nilaiTugas = round($sumNilaiTugas / $totalTugas, 2);
                }

                $uts = $mhs->pivot->nilai_uts;
                $uas = $mhs->pivot->nilai_uas;

                $akhirAngka = $mhs->pivot->nilai_akhir_angka;
                $huruf      = $mhs->pivot->nilai;

                // Hanya hitung ulang Nilai Akhir & Huruf jika nilai UTS atau UAS sudah ada,
                // agar tarik data Verum di awal/tengah semester tidak menjatuhkan nilai huruf 'E' prematur.
                if ($uts !== null || $uas !== null) {
                    $akhirAngka = $this->hitungNilaiAkhir(
                        (float) ($nilaiHadir ?? 0),
                        (float) ($nilaiTugas ?? 0),
                        (float) ($uts ?? 0),
                        (float) ($uas ?? 0)
                    );
                    $huruf = $this->konversiAngkaKeHuruf($akhirAngka);
                }

                DB::table('mahasiswa_mata_kuliah')
                    ->where('mahasiswa_id', $mhs->id)
                    ->where('mata_kuliah_id', $mataKuliah->id)
                    ->where('tahun_akademik_id', $tahunAkademik->id)
                    ->update([
                        'nilai_kehadiran'   => $nilaiHadir,
                        'nilai_tugas'       => $nilaiTugas,
                        'nilai_akhir_angka' => $akhirAngka,
                        'nilai'             => $huruf,
                        'updated_at'        => now(),
                    ]);

                $updatedCount++;
            }
        });

        return $updatedCount;
    }
}