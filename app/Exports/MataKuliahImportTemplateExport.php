<?php

namespace App\Exports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class MataKuliahImportTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    public function title(): string
    {
        return 'Template Mata Kuliah';
    }

    public function collection(): Collection
    {
        return new Collection([
            [
                'kode_mk' => 'MK001',
                'nama_mk' => 'Pengantar Teologi',
                'sks' => '3',
                'semester' => '1',
                'nidn_dosen' => 'Isi NIDN (Lihat Kolom G)', 
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'kode_mk', 'nama_mk', 'sks', 'semester', 'nidn_dosen',
            '', // Spasi F
            'REFERENSI DOSEN (NIDN - NAMA)', // G
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Kode MK
            'E' => NumberFormat::FORMAT_TEXT, // NIDN Input
            'G' => NumberFormat::FORMAT_TEXT, // NIDN Ref
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // Header Styles
                $sheet->getStyle('A1:E1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']],
                ]);
                $sheet->getStyle('G1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF64748B']],
                ]);

                // REFERENSI DOSEN (NIDN)
                $dosens = Dosen::select('nidn', 'nama_lengkap')->get();
                $row = 2;
                foreach($dosens as $dosen) {
                    $sheet->setCellValueExplicit('G' . $row, $dosen->nidn . ' - ' . $dosen->nama_lengkap, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $row++;
                }

                $sheet->getColumnDimension('F')->setWidth(2);
                $sheet->getColumnDimension('G')->setAutoSize(true);
            }
        ];
    }
}