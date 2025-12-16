<?php

namespace App\Exports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class DosensExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    public function collection()
    {
        return Dosen::all();
    }

    public function map($dosen): array
    {
        return [
            $dosen->nidn,
            $dosen->nama_lengkap,
            $dosen->email_institusi,
            $dosen->jabatan_akademik,
            $dosen->bidang_keahlian,
            $dosen->link_google_scholar,
            $dosen->link_sinta,
        ];
    }

    public function headings(): array
    {
        return [
            'NIDN', 'Nama Lengkap', 'Email Institusi', 'Jabatan Akademik', 
            'Bidang Keahlian', 'Link Google Scholar', 'Link SINTA'
        ];
    }
}