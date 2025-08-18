<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class DosenImportTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    /**
     * Menentukan judul sheet.
     */
    public function title(): string
    {
        return 'Template Impor Dosen';
    }

    /**
     * Menambahkan contoh data untuk template.
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        // Menyediakan satu baris contoh data
        return new Collection([
            [
                'nidn' => '0912345601',
                'nama_lengkap' => 'Dr. Budi Santoso, M.Kom.',
                'email' => 'budi.santoso@example.com',
                'password' => 'password123',
            ]
        ]);
    }

    /**
     * Menentukan judul kolom.
     */
    public function headings(): array
    {
        return [
            'nidn',
            'nama_lengkap',
            'email',
            'password',
        ];
    }

    /**
     * Menentukan format spesifik untuk kolom.
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Format kolom NIDN sebagai Teks
        ];
    }
}