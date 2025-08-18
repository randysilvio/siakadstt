<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class MahasiswaImportTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    /**
     * Menentukan judul sheet.
     */
    public function title(): string
    {
        return 'Template Impor Mahasiswa';
    }
    
    /**
     * Menambahkan contoh data untuk template.
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        return new Collection([
            [
                'nim' => '202401001',
                'nama_lengkap' => 'Budi Setiawan',
                'program_studi_id' => 1,
                'dosen_wali_id' => 1,
                'email' => 'budi.setiawan@example.com',
                'password' => 'password123',
                'tempat_lahir' => 'Makassar',
                'tanggal_lahir' => '2005-08-17',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Merdeka No. 10',
                'nomor_telepon' => '081234567890',
                'tahun_masuk' => '2024',
            ]
        ]);
    }

    /**
     * Menentukan judul kolom.
     */
    public function headings(): array
    {
        return [
            'nim',
            'nama_lengkap',
            'program_studi_id',
            'dosen_wali_id',
            'email',
            'password',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'nomor_telepon',
            'tahun_masuk',
        ];
    }

    /**
     * Menentukan format spesifik untuk kolom.
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Format kolom NIM sebagai Teks
            'H' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Format tanggal lahir
            'K' => NumberFormat::FORMAT_TEXT, // Format nomor telepon sebagai Teks
            'L' => NumberFormat::FORMAT_TEXT, // Format tahun masuk sebagai Teks
        ];
    }

    /**
     * Mendaftarkan event untuk styling.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Style untuk header kolom
                $event->sheet->getStyle('A1:L1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);
            },
        ];
    }
}