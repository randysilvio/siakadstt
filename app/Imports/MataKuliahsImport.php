<?php

namespace App\Imports;

use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\ProgramStudi;
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
        // 1. Skip jika kode kosong
        if (empty($row['kode_mk']) || empty($row['nama_mata_kuliah'])) {
            return null;
        }

        // 2. LOGIKA CERDAS: DOSEN PENGAMPU
        $dosenId = null;
        if (!empty($row['dosen_pengampu'])) {
            // Jika Angka (NIDN/ID)
            if (is_numeric($row['dosen_pengampu'])) {
                $dosen = Dosen::where('nidn', $row['dosen_pengampu'])->orWhere('id', $row['dosen_pengampu'])->first();
                $dosenId = $dosen ? $dosen->id : null;
            } 
            // Jika Teks (Nama)
            else {
                $dosen = Dosen::where('nama_lengkap', 'LIKE', '%' . $row['dosen_pengampu'] . '%')->first();
                $dosenId = $dosen ? $dosen->id : null;
            }
        }

        // 3. LOGIKA CERDAS: PROGRAM STUDI
        $prodiId = null;
        if (!empty($row['program_studi'])) {
            if (is_numeric($row['program_studi'])) {
                $prodiId = $row['program_studi'];
            } else {
                $prodi = ProgramStudi::where('nama_prodi', 'LIKE', '%' . $row['program_studi'] . '%')->first();
                $prodiId = $prodi ? $prodi->id : null;
            }
        }

        // 4. Simpan / Update MK
        return MataKuliah::updateOrCreate(
            ['kode_mk' => trim($row['kode_mk'])],
            [
                'nama_mk'      => trim($row['nama_mata_kuliah']),
                'sks'          => $row['sks'] ?? 2,
                'semester'     => $row['semester'] ?? 1,
                'prodi_id'     => $prodiId,
                'dosen_id'     => $dosenId, // Hasil logika cerdas di atas
                'kurikulum_id' => $row['kurikulum_id'] ?? null,
                'deskripsi'    => $row['deskripsi'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'kode_mk' => 'required',
            'nama_mata_kuliah' => 'required',
        ];
    }
}