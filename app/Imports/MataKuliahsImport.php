<?php

namespace App\Imports;

use App\Models\MataKuliah;
use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class MataKuliahsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    private $dosens;

    public function __construct()
    {
        // Cache NIDN -> ID
        $this->dosens = Dosen::pluck('id', 'nidn');
    }
    
    public function model(array $row)
    {
        if (!isset($row['kode_mk']) || !isset($row['nama_mk'])) {
            return null;
        }

        $kodeMK = trim((string) $row['kode_mk']);
        
        // Cari ID Dosen di cache (tanpa query database berulang)
        $nidn = trim((string) ($row['nidn_dosen'] ?? ''));
        $dosenId = $this->dosens->get($nidn); 

        return MataKuliah::updateOrCreate(
            ['kode_mk' => $kodeMK],
            [
                'nama_mk'   => $row['nama_mk'],
                'sks'       => $row['sks'],
                'semester'  => $row['semester'],
                'dosen_id'  => $dosenId,
                'kurikulum_id' => $row['kurikulum_id'] ?? null, 
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
        ];
    }
}