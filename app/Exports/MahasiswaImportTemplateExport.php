<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class MahasiswaImportTemplateExport implements WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    /**
     * Menentukan judul sheet di dalam file Excel.
     */
    public function title(): string
    {
        return 'Template Impor Mahasiswa';
    }

    /**
     * Menentukan judul kolom di file Excel.
     */
    public function headings(): array
    {
        // Baris pertama sengaja dikosongkan untuk diisi oleh kop
        return [
            [], 
            [
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
            ]
        ];
    }

    /**
     * Mendaftarkan event untuk memanipulasi sheet setelah dibuat.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Menggabungkan sel dari A1 hingga L1 untuk judul
                $event->sheet->mergeCells('A1:L1');
                
                // Menambahkan judul utama
                $event->sheet->setCellValue('A1', 'INPUT DATA MAHASISWA STT GPI PAPUA');

                // Mengatur style untuk judul
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Mengatur style untuk header kolom (baris ke-2)
                $event->sheet->getStyle('A2:L2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);
            },
        ];
    }
}
