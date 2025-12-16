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
    public function title(): string
    {
        return 'Template Impor Dosen';
    }

    public function collection(): Collection
    {
        return new Collection([
            [
                'nidn' => '0912345601',
                'nama_lengkap' => 'Dr. Budi Santoso, M.Kom.',
                'email' => 'budi.santoso@example.com',
                'password' => 'password123',
                'jabatan_akademik' => 'Lektor Kepala',
                'bidang_keahlian' => 'Rekayasa Perangkat Lunak, AI',
                'deskripsi_diri' => 'Dosen senior dengan pengalaman mengajar.',
                'email_institusi' => 'budi.s@sttgpipapua.ac.id',
                'link_google_scholar' => 'https://scholar.google.com/citations?user=xxxx',
                'link_sinta' => 'https://sinta.kemdikbud.go.id/authors/profile/xxxx',
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

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIDN Text
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                // Style Header (Teal)
                $sheet->getStyle('A1:J1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']],
                ]);
            },
        ];
    }
}