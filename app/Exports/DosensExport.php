<?php

namespace App\Exports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DosensExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        // PERBAIKAN: Memilih semua kolom yang relevan untuk diekspor
        return Dosen::select(
            'nidn', 
            'nama_lengkap', 
            'email_institusi', 
            'jabatan_akademik', 
            'bidang_keahlian', 
            'link_google_scholar', 
            'link_sinta'
        )->get();
    }

    public function headings(): array
    {
        // PERBAIKAN: Menambahkan header kolom yang sesuai
        return [
            'NIDN',
            'Nama Lengkap',
            'Email Institusi',
            'Jabatan Akademik',
            'Bidang Keahlian',
            'Link Google Scholar',
            'Link SINTA',
        ];
    }
}