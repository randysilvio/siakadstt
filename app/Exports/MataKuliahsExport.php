<?php

namespace App\Exports;

use App\Models\MataKuliah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MataKuliahsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return MataKuliah::with(['dosen', 'programStudi'])->get();
    }

    public function map($mk): array
    {
        return [
            $mk->kode_mk,
            $mk->nama_mk,
            $mk->sks,
            $mk->semester,
            $mk->programStudi->nama_prodi ?? '-',
            $mk->dosen->nama_lengkap ?? '-',
            $mk->kurikulum_id,
            $mk->deskripsi,
        ];
    }

    public function headings(): array
    {
        return [
            ['DATA EKSPOR MATA KULIAH'],
            [
                'Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 
                'Program Studi', 'Dosen Pengampu', 'Kurikulum ID', 'Deskripsi'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
        ];
    }
}