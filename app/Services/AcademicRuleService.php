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
    /**
     * Batas SKS KRS semester aktif (kebijakan kampus berdasarkan SN Dikti, Permendikbudristek 53/2023 Pasal 18):
     *  - Semester ke-1: paling banyak 20 SKS.
     *  - Semester ke-2 dst.: menurut IPK kumulatif, paling banyak 24 SKS
     *    (IPK ≥ 3,00 → 24; 2,50–2,99 → 21; 2,00–2,49 → 18; < 2,00 → 15).
     *  - Semester ke-2 dst. yang nilainya belum keluar sama sekali: 20 SKS (sama dengan semester 1).
     *
     * @return array{ipk: float, max_sks: int, semester_ke: int}
     */
    public function hitungBatasSks(Mahasiswa $mahasiswa): array
    {
        $ipk = $mahasiswa->hitungIpk();
        $semesterKe = $this->semesterKe($mahasiswa);

        if ($semesterKe <= 1 || !$mahasiswa->sudahPunyaNilai()) {
            $maxSks = 20;
        } elseif ($ipk >= 3.00) {
            $maxSks = 24;
        } elseif ($ipk >= 2.50) {
            $maxSks = 21;
        } elseif ($ipk >= 2.00) {
            $maxSks = 18;
        } else {
            $maxSks = 15;
        }

        return [
            'ipk'         => $ipk,
            'max_sks'     => $maxSks,
            'semester_ke' => $semesterKe,
        ];
    }

    /**
     * Semester ke berapa mahasiswa pada periode aktif = jumlah semester sebelumnya yang benar-benar ditempuh
     * (KRS disetujui atau sudah bernilai) + 1. Semester cuti / tanpa KRS tidak dihitung.
     */
    public function semesterKe(Mahasiswa $mahasiswa): int
    {
        $aktifId = (int) TahunAkademik::where('is_active', true)->value('id');

        $dariNilai = DB::table('mahasiswa_mata_kuliah')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', '!=', $aktifId)
            ->whereNotNull('nilai')
            ->distinct()
            ->pluck('tahun_akademik_id');

        $dariPersetujuan = \App\Models\KrsPersetujuan::where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', '!=', $aktifId)
            ->where('status', \App\Models\KrsPersetujuan::DISETUJUI)
            ->whereIn('tahun_akademik_id', DB::table('mahasiswa_mata_kuliah')->where('mahasiswa_id', $mahasiswa->id)->select('tahun_akademik_id'))
            ->pluck('tahun_akademik_id');

        return $dariNilai->merge($dariPersetujuan)->unique()->count() + 1;
    }

    /**
     * Semester kurikulum yang ditawarkan pada periode aktif (Ganjil: 1,3,5,7 — Genap: 2,4,6,8).
     */
    public function semesterDitawarkan(TahunAkademik $periodeAktif): array
    {
        return in_array($periodeAktif->semester, ['Ganjil', 'Gasal']) ? [1, 3, 5, 7] : [2, 4, 6, 8];
    }

    /**
     * Query mata kuliah yang boleh diambil mahasiswa: kurikulum AKTIF milik prodinya (atau kurikulum umum
     * tanpa prodi) pada semester yang ditawarkan. Tidak ada lagi fallback ke seluruh mata kuliah.
     */
    public function mataKuliahDitawarkanQuery(Mahasiswa $mahasiswa, TahunAkademik $periodeAktif)
    {
        return MataKuliah::query()
            ->whereIn('semester', $this->semesterDitawarkan($periodeAktif))
            ->whereHas('kurikulum', function ($q) use ($mahasiswa) {
                $q->where('is_active', true)
                  ->where(function ($sub) use ($mahasiswa) {
                      $sub->where('program_studi_id', $mahasiswa->program_studi_id)
                          ->orWhereNull('program_studi_id');
                  });
            });
    }

    /**
     * Memvalidasi aturan pengajuan KRS (Kurikulum Prodi, Semester, Batas SKS, Prasyarat, Rombel, Kuota Kursi Kelas Paralel, & Bentrok Jadwal Rombel).
     */
    public function validateKrsRules(Mahasiswa $mahasiswa, TahunAkademik $periodeAktif, array $mataKuliahIds, int $maxSks, array $pilihanKelas = []): array
    {
        if (empty($mataKuliahIds)) {
            return [];
        }

        $allowedSemesters = $this->semesterDitawarkan($periodeAktif);
        $mkDipilih = MataKuliah::with(['prasyarats', 'jadwals'])->whereIn('id', $mataKuliahIds)->get();

        if ($mkDipilih->count() !== count(array_unique($mataKuliahIds))) {
            return ['Terdapat mata kuliah yang tidak valid di dalam pilihan KRS.'];
        }

        $ditawarkanIds = $this->mataKuliahDitawarkanQuery($mahasiswa, $periodeAktif)
            ->whereIn('id', $mataKuliahIds)
            ->pluck('id')
            ->all();
        $tidakDitawarkan = $mkDipilih->whereNotIn('id', $ditawarkanIds);
        if ($tidakDitawarkan->isNotEmpty()) {
            return ['Mata kuliah berikut bukan bagian dari kurikulum aktif program studi Anda: ' . $tidakDitawarkan->pluck('nama_mk')->implode(', ') . '.'];
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
                if (!$slotDipilih && $mk->jadwals->isNotEmpty()) {
                    return ["Kelas {$klsPilihan} tidak tersedia untuk mata kuliah {$mk->nama_mk}."];
                }
            }
            if (!$slotDipilih) {
                $slotDipilih = $mk->jadwals->first();
            }

            if ($slotDipilih) {
                $namaKlsAktual = strtoupper($slotDipilih->nama_kelas ?? 'A');
                $kuotaMaks = (int) ($slotDipilih->kuota ?? 40);

                // lockForUpdate: dipanggil di dalam transaksi simpan KRS agar dua pengajuan bersamaan tidak melampaui kuota
                $terisiSaatIni = DB::table('mahasiswa_mata_kuliah')
                    ->lockForUpdate()
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

    /**
     * Menerapkan keputusan validasi KRS (oleh Dosen Wali / Kaprodi / Admin) untuk semester aktif.
     * Aturan: salah satu validator cukup; KRS yang sudah Disetujui hanya dapat dibuka kembali oleh validator
     * yang menyetujuinya atau Admin, agar dosen wali & Kaprodi tidak saling membatalkan keputusan.
     *
     * @return string|null pesan kesalahan, atau null bila berhasil
     */
    public function terapkanValidasiKrs(Mahasiswa $mahasiswa, TahunAkademik $periodeAktif, string $statusBaru, ?string $catatan, \App\Models\User $oleh): ?string
    {
        $punyaKrs = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
        if (!$punyaKrs) {
            return 'Mahasiswa ini belum mengajukan mata kuliah apa pun pada semester ' . $periodeAktif->nama_semester . '.';
        }

        $persetujuan = $mahasiswa->persetujuanKrsPada($periodeAktif->id);
        if ($persetujuan && $persetujuan->status === \App\Models\KrsPersetujuan::DISETUJUI && $statusBaru !== \App\Models\KrsPersetujuan::DISETUJUI) {
            $bolehBuka = $oleh->hasRole('admin') || (int) $persetujuan->divalidasi_oleh === (int) $oleh->id;
            if (!$bolehBuka) {
                $nama = optional($persetujuan->validator)->name ?? 'validator lain';
                return "KRS ini sudah DISETUJUI oleh {$nama}. Hanya validator tersebut atau Admin yang dapat membukanya kembali.";
            }
        }

        $catatanBersih = $catatan !== null && trim($catatan) !== '' ? strip_tags($catatan) : null;
        if ($statusBaru === \App\Models\KrsPersetujuan::DISETUJUI && $catatanBersih === null) {
            $catatanBersih = null;
        }

        $mahasiswa->setStatusKrs($periodeAktif->id, $statusBaru, $catatanBersih, $oleh->id);

        if ($mahasiswa->user && in_array($statusBaru, [\App\Models\KrsPersetujuan::DISETUJUI, \App\Models\KrsPersetujuan::DITOLAK], true)) {
            $mahasiswa->user->notify(new \App\Notifications\KrsValidated($mahasiswa->fresh(), $statusBaru));
        }

        return null;
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

    /**
     * Menentukan nilai akhir angka & huruf mutu dari komponen penilaian.
     *  - Huruf manual dari dosen selalu dihormati.
     *  - Konversi otomatis ke huruf hanya dilakukan bila UTS atau UAS sudah ada, sehingga input kehadiran/tugas
     *    di tengah semester tidak menghasilkan nilai huruf (dan IPK) prematur.
     *
     * @return array{angka: float|null, huruf: string|null}
     */
    public function tentukanNilai(?float $hadir, ?float $tugas, ?float $uts, ?float $uas, ?string $hurufManual = null): array
    {
        $adaKomponen = ($hadir !== null || $tugas !== null || $uts !== null || $uas !== null);
        $angka = $adaKomponen
            ? $this->hitungNilaiAkhir($hadir ?? 0, $tugas ?? 0, $uts ?? 0, $uas ?? 0)
            : null;

        if ($hurufManual !== null && $hurufManual !== '') {
            return ['angka' => $angka, 'huruf' => strtoupper($hurufManual)];
        }

        $huruf = ($uts !== null || $uas !== null) ? $this->konversiAngkaKeHuruf((float) $angka) : null;

        return ['angka' => $angka, 'huruf' => $huruf];
    }

    /**
     * Menarik nilai kehadiran & tugas dari kelas Verum ke buku nilai KRS.
     * Setiap mahasiswa hanya dihitung dari kelas Verum rombelnya sendiri (atau kelas Verum tanpa rombel,
     * yang berlaku untuk semua rombel), sehingga kelas paralel tidak saling mengencerkan nilai.
     */
    public function syncNilaiVerumKeKrs(MataKuliah $mataKuliah, TahunAkademik $tahunAkademik): int
    {
        $verumKelas = VerumKelas::where('mata_kuliah_id', $mataKuliah->id)
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->get(['id', 'rombel']);

        if ($verumKelas->isEmpty()) {
            return 0;
        }

        $mahasiswas = $mataKuliah->mahasiswas()
            ->wherePivot('tahun_akademik_id', $tahunAkademik->id)
            ->whereIn('mahasiswas.id', \App\Models\KrsPersetujuan::idsDisetujui((int) $tahunAkademik->id))
            ->get();

        $updatedCount = 0;

        DB::transaction(function () use ($mahasiswas, $mataKuliah, $tahunAkademik, $verumKelas, &$updatedCount) {
            foreach ($mahasiswas as $mhs) {
                $rombelMhs = strtoupper(trim((string) ($mhs->pivot->nama_kelas ?? 'A')));
                $kelasIds = $verumKelas
                    ->filter(fn ($k) => $k->rombel === null || $k->rombel === '' || strtoupper(trim($k->rombel)) === $rombelMhs)
                    ->pluck('id');

                if ($kelasIds->isEmpty()) {
                    continue;
                }

                $presensiIds = DB::table('verum_presensi')->whereIn('kelas_id', $kelasIds)->pluck('id');
                // Hanya tugas yang sudah dinilai dosen (minimal satu jawaban bernilai) yang dihitung. Tugas yang belum
                // dinilai, atau dikumpulkan luring sehingga tidak ada jawaban di Verum, tidak boleh menimpa nilai
                // tugas di Buku Nilai menjadi 0. Mahasiswa yang tidak mengumpulkan tugas yang sudah dinilai tetap 0.
                $tugasIds = DB::table('verum_tugas')
                    ->whereIn('kelas_id', $kelasIds)
                    ->whereExists(fn ($q) => $q->from('verum_pengumpulan')
                        ->whereColumn('verum_pengumpulan.tugas_id', 'verum_tugas.id')
                        ->whereNotNull('verum_pengumpulan.nilai'))
                    ->pluck('id');

                $nilaiHadir = $mhs->pivot->nilai_kehadiran;
                if ($presensiIds->count() > 0) {
                    $jmlHadir = DB::table('verum_kehadiran')
                        ->whereIn('presensi_id', $presensiIds)
                        ->where('mahasiswa_id', $mhs->id)
                        ->where('status', 'hadir')
                        ->count();
                    $nilaiHadir = round(($jmlHadir / $presensiIds->count()) * 100, 2);
                }

                $nilaiTugas = $mhs->pivot->nilai_tugas;
                if ($tugasIds->count() > 0) {
                    $sumNilaiTugas = VerumPengumpulan::whereIn('tugas_id', $tugasIds)
                        ->where('mahasiswa_id', $mhs->id)
                        ->whereNotNull('nilai')
                        ->sum('nilai');
                    $nilaiTugas = round($sumNilaiTugas / $tugasIds->count(), 2);
                }

                $uts = $mhs->pivot->nilai_uts;
                $uas = $mhs->pivot->nilai_uas;
                $hurufManual = !empty($mhs->pivot->nilai_manual) ? $mhs->pivot->nilai : null;

                $hasil = $this->tentukanNilai(
                    $nilaiHadir !== null ? (float) $nilaiHadir : null,
                    $nilaiTugas !== null ? (float) $nilaiTugas : null,
                    $uts !== null ? (float) $uts : null,
                    $uas !== null ? (float) $uas : null,
                    $hurufManual
                );

                DB::table('mahasiswa_mata_kuliah')
                    ->where('mahasiswa_id', $mhs->id)
                    ->where('mata_kuliah_id', $mataKuliah->id)
                    ->where('tahun_akademik_id', $tahunAkademik->id)
                    ->update([
                        'nilai_kehadiran'   => $nilaiHadir,
                        'nilai_tugas'       => $nilaiTugas,
                        'nilai_akhir_angka' => $hasil['angka'],
                        'nilai'             => $hasil['huruf'],
                        'updated_at'        => now(),
                    ]);

                $updatedCount++;
            }
        });

        return $updatedCount;
    }
}
