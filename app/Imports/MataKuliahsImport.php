<?php

namespace App\Imports;

use App\Imports\Concerns\ResolveReferensiImpor;
use App\Models\Jadwal;
use App\Models\Kurikulum;
use App\Models\MataKuliah;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Impor mata kuliah. Referensi (prodi, kurikulum, dosen) dicocokkan TEPAT; baris yang tidak cocok
 * menggagalkan impor dengan pesan per baris, tidak lagi "jatuh" ke kurikulum/dosen lain.
 */
class MataKuliahsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    use ResolveReferensiImpor;

    private const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $this->majukanBaris();

        if (empty($row['kode_mk']) || empty($row['nama_mata_kuliah'])) {
            return null;
        }

        $kode = trim((string) $row['kode_mk']);
        $mkLama = MataKuliah::where('kode_mk', $kode)->first();

        // 1. Kurikulum: ID kurikulum (harus milik prodi bila prodi diisi) atau kurikulum prodi (aktif didahulukan)
        $prodi = null;
        if (!empty($row['program_studi'])) {
            $prodi = $this->cariProdi($row['program_studi'])
                ?? $this->gagal("program studi '" . trim((string) $row['program_studi']) . "' tidak ditemukan. Gunakan ID, kode, atau nama prodi persis.");
        }

        $kurikulum = null;
        if (!empty($row['kurikulum_id'])) {
            $kurikulum = Kurikulum::find((int) $row['kurikulum_id'])
                ?? $this->gagal("kurikulum ID {$row['kurikulum_id']} tidak ditemukan.");
            if ($prodi && $kurikulum->program_studi_id && (int) $kurikulum->program_studi_id !== $prodi->id) {
                $this->gagal("kurikulum ID {$kurikulum->id} bukan milik program studi {$prodi->nama_prodi}.");
            }
        } elseif ($prodi) {
            $kurikulum = Kurikulum::where('program_studi_id', $prodi->id)->orderByDesc('is_active')->orderByDesc('tahun')->first()
                ?? $this->gagal("program studi {$prodi->nama_prodi} belum memiliki kurikulum. Buat kurikulum terlebih dahulu.");
        } elseif ($mkLama) {
            $kurikulum = $mkLama->kurikulum;
        }

        if (!$kurikulum) {
            $this->gagal('isi kolom Program Studi atau Kurikulum ID agar mata kuliah masuk ke kurikulum yang benar.');
        }

        // 2. Dosen pengampu (kosong = pertahankan pengampu lama)
        $dosenId = $mkLama->dosen_id ?? null;
        if (!empty($row['dosen_pengampu'])) {
            $dosen = $this->cariDosen($row['dosen_pengampu'])
                ?? $this->gagal("dosen pengampu '" . trim((string) $row['dosen_pengampu']) . "' tidak ditemukan. Gunakan NIDN atau nama lengkap persis.");
            $dosenId = $dosen->id;
        }

        $sks = (int) ($row['sks'] ?? 0);
        $semester = (int) ($row['semester'] ?? 0);
        if ($sks < 1 || $sks > 24) {
            $this->gagal('SKS wajib diisi angka 1–24.');
        }
        if ($semester < 1 || $semester > 8) {
            $this->gagal('semester wajib diisi angka 1–8.');
        }

        $mk = MataKuliah::updateOrCreate(
            ['kode_mk' => $kode],
            [
                'nama_mk'      => trim((string) $row['nama_mata_kuliah']),
                'sks'          => $sks,
                'semester'     => $semester,
                'dosen_id'     => $dosenId,
                'kurikulum_id' => $kurikulum->id,
            ]
        );

        if ($dosenId) {
            $mk->teamDosens()->syncWithoutDetaching([$dosenId => ['is_utama' => true]]);
        }

        // 3. Jadwal rombel A (kunci: MK + hari + rombel, agar kelas paralel tidak saling menimpa)
        if (!empty($row['hari']) && !empty($row['jam_mulai']) && !empty($row['jam_selesai'])) {
            $hari = ucfirst(strtolower(trim((string) $row['hari'])));
            if (!in_array($hari, self::HARI, true)) {
                $this->gagal("hari '{$row['hari']}' tidak dikenal. Pilihan: " . implode(', ', self::HARI) . '.');
            }

            Jadwal::updateOrCreate(
                ['mata_kuliah_id' => $mk->id, 'hari' => $hari, 'nama_kelas' => 'A'],
                [
                    'jam_mulai'   => $this->jam($row['jam_mulai']),
                    'jam_selesai' => $this->jam($row['jam_selesai']),
                    'ruang'       => !empty($row['ruang']) ? trim((string) $row['ruang']) : null,
                ]
            );
        }

        return $mk;
    }

    /** Jam dari Excel bisa berupa pecahan hari (0.333 = 08:00) atau teks "08:00". */
    private function jam($nilai): string
    {
        if (is_numeric($nilai) && (float) $nilai < 1) {
            $menit = (int) round((float) $nilai * 24 * 60);
            return sprintf('%02d:%02d', intdiv($menit, 60), $menit % 60);
        }
        return trim((string) $nilai);
    }

    public function rules(): array
    {
        return [
            'kode_mk' => 'required',
            'nama_mata_kuliah' => 'required',
        ];
    }
}
