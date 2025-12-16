<?php

namespace App\Imports;

use App\Models\MataKuliah;
use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MataKuliahsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $dosens;

    public function __construct()
    {
        // Cache NIDN -> ID
        $this->dosens = Dosen::pluck('id', 'nidn');
    }
    
    public function model(array $row)
    {
        $nidn = trim((string) $row['nidn_dosen']);
        $dosenId = $this->dosens->get($nidn);
        $kodeMK = trim((string) $row['kode_mk']);

        // Jika kode MK sudah ada, update. Jika belum, create.
        return MataKuliah::updateOrCreate(
            ['kode_mk' => $kodeMK],
            [
                'nama_mk'   => $row['nama_mk'],
                'sks'       => $row['sks'],
                'semester'  => $row['semester'],
                'dosen_id'  => $dosenId, // Bisa null jika NIDN tidak ditemukan di DB
            ]
        );
    }

    public function rules(): array
    {
        return [
            'kode_mk'    => 'required',
            'nama_mk'    => 'required',
            'sks'        => 'required|integer',
            'semester'   => 'required|integer',
            'nidn_dosen' => 'required', // Validasi 'exists' dihapus agar row tidak gagal total, tapi dosen_id jadi null
        ];
    }
}