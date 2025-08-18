<?php

namespace App\Exports;

use App\Models\MataKuliah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MataKuliahsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return MataKuliah::with('dosen')->get()->map(function(MataKuliah $mk) {
            return [
                'kode_mk' => $mk->kode_mk,
                'nama_mk' => $mk->nama_mk,
                'sks' => $mk->sks,
                'semester' => $mk->semester,
                'dosen_pengampu' => $mk->dosen->nama_lengkap ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return ['Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Dosen Pengampu'];
    }
}