<?php

namespace App\Imports;

use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\Kurikulum;
use App\Models\Jadwal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class MataKuliahsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        if (empty($row['kode_mk']) || empty($row['nama_mata_kuliah'])) {
            return null;
        }

        // 1. Cari Dosen Pengampu
        $dosenId = null;
        if (!empty($row['dosen_pengampu'])) {
            $inputDosen = ltrim(trim((string) $row['dosen_pengampu']), "'");
            if (is_numeric($inputDosen)) {
                $dosen = Dosen::where('nidn', $inputDosen)->orWhere('id', $inputDosen)->first();
                $dosenId = $dosen ? $dosen->id : null;
            } else {
                $dosen = Dosen::where('nama_lengkap', 'LIKE', '%' . $inputDosen . '%')->first();
                $dosenId = $dosen ? $dosen->id : null;
            }
        }

        // 2. Cari Program Studi & Kurikulum
        $prodiId = null;
        if (!empty($row['program_studi'])) {
            if (is_numeric($row['program_studi'])) {
                $prodiId = (int) $row['program_studi'];
            } else {
                $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . trim($row['program_studi']) . '%')->first();
                $prodiId = $prodi ? $prodi->id : null;
            }
        }

        $kurikulumId = !empty($row['kurikulum_id']) ? (int) $row['kurikulum_id'] : null;
        if (!$kurikulumId && $prodiId) {
            $kurikulum = Kurikulum::where('program_studi_id', $prodiId)->orderBy('is_active', 'desc')->first();
            $kurikulumId = $kurikulum ? $kurikulum->id : null;
        }
        if (!$kurikulumId) {
            $defaultKurikulum = Kurikulum::orderBy('is_active', 'desc')->first();
            $kurikulumId = $defaultKurikulum ? $defaultKurikulum->id : null;
        }

        // 3. Simpan / Update MK sesuai kolom asli tabel mata_kuliahs
        $mk = MataKuliah::updateOrCreate(
            ['kode_mk' => trim((string) $row['kode_mk'])],
            [
                'nama_mk'      => trim((string) $row['nama_mata_kuliah']),
                'sks'          => (int) ($row['sks'] ?? 2),
                'semester'     => (int) ($row['semester'] ?? 1),
                'dosen_id'     => $dosenId,
                'kurikulum_id' => $kurikulumId,
            ]
        );

        // Sinkronisasi ke tabel pivot dosen_mata_kuliah jika ada dosen pengampu
        if ($dosenId) {
            $mk->teamDosens()->syncWithoutDetaching([$dosenId => ['is_utama' => true]]);
        }

        // 4. Logika Jadwal
        if (!empty($row['hari']) && !empty($row['jam_mulai']) && !empty($row['jam_selesai'])) {
            Jadwal::updateOrCreate(
                [
                    'mata_kuliah_id' => $mk->id,
                    'hari'           => trim((string) $row['hari'])
                ],
                [
                    'jam_mulai'   => trim((string) $row['jam_mulai']),
                    'jam_selesai' => trim((string) $row['jam_selesai']),
                    'ruang'       => !empty($row['ruang']) ? trim((string) $row['ruang']) : null,
                ]
            );
        }

        return $mk;
    }

    public function rules(): array
    {
        return [
            'kode_mk' => 'required',
            'nama_mata_kuliah' => 'required',
        ];
    }
}