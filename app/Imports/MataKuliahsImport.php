<?php

namespace App\Imports;

use App\Models\MataKuliah;
use App\Models\Dosen; // Tambahkan ini
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MataKuliahsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $dosens;

    public function __construct()
    {
        // Ambil semua data dosen untuk pencocokan NIDN
        $this->dosens = Dosen::pluck('id', 'nidn');
    }
    
    public function model(array $row)
    {
        // Cari ID dosen berdasarkan NIDN dari file Excel
        $dosenId = $this->dosens->get($row['nidn_dosen']);

        return new MataKuliah([
            'kode_mk' => $row['kode_mk'],
            'nama_mk' => $row['nama_mk'],
            'sks' => $row['sks'],
            'semester' => $row['semester'],
            'dosen_id' => $dosenId, // Gunakan ID dosen yang ditemukan
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_mk' => 'required|string|unique:mata_kuliahs,kode_mk',
            'nama_mk' => 'required|string',
            'sks' => 'required|integer',
            'semester' => 'required|integer',
            'nidn_dosen' => 'required|exists:dosens,nidn', // Validasi NIDN dosen harus ada
        ];
    }
}