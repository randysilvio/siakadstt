<?php

namespace App\Exports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DosensExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Dosen::select('nidn', 'nama_lengkap')->get();
    }

    public function headings(): array
    {
        return ['NIDN', 'Nama Lengkap'];
    }
}