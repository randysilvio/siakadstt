<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DosenImportTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    public function title(): string { return 'Template Dosen'; }

    public function collection(): Collection
    {
        return new Collection([
            [
                'nidn' => '0912345601',
                'nama_lengkap' => 'Dr. Budi Santoso',
                'email' => 'budi@contoh.com',
                'password' => 'password123',
                'jabatan_akademik' => 'Lektor',
                'bidang_keahlian' => 'Teologi',
                'deskripsi_diri' => 'Dosen Tetap',
                'email_institusi' => 'budi@kampus.ac.id',
                'link_google_scholar' => '',
                'link_sinta' => '',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'nidn', 'nama_lengkap', 'email', 'password',
            'jabatan_akademik', 'bidang_keahlian', 'deskripsi_diri',
            'email_institusi', 'link_google_scholar', 'link_sinta',
        ];
    }

    public function columnFormats(): array { return ['A' => NumberFormat::FORMAT_TEXT]; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:J1')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']]]);
            },
        ];
    }
}