<?php

namespace App\Exports;

use App\Models\Dosen;
use App\Models\Kurikulum;
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
    public function title(): string { return 'Template Mata Kuliah'; }

    public function collection(): Collection
    {
        return new Collection([
            [
                'kode_mk' => 'MK001',
                'nama_mk' => 'Pengantar Teologi',
                'sks' => '3',
                'semester' => '1',
                'nidn_dosen' => '00123456', 
                'kurikulum_id' => '1',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'kode_mk', 'nama_mk', 'sks', 'semester', 'nidn_dosen', 'kurikulum_id',
            '', 'REF: NIDN DOSEN', 'REF: ID KURIKULUM'
        ];
    }

    public function columnFormats(): array
    {
        return [ 'A' => NumberFormat::FORMAT_TEXT, 'E' => NumberFormat::FORMAT_TEXT, 'H' => NumberFormat::FORMAT_TEXT ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $sheet->getStyle('A1:F1')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']]]);
                $sheet->getStyle('H1:I1')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF475569']]]);

                // REF DATA
                $dosens = Dosen::select('nidn', 'nama_lengkap')->get();
                $row = 2;
                foreach($dosens as $dosen) {
                    $sheet->setCellValueExplicit('H' . $row, $dosen->nidn . ' - ' . $dosen->nama_lengkap, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $row++;
                }
                
                $kurikulums = Kurikulum::select('id', 'nama_kurikulum', 'tahun')->orderBy('tahun', 'desc')->get();
                $row = 2;
                foreach($kurikulums as $k) {
                    $sheet->setCellValue('I' . $row, $k->id . ' - ' . $k->nama_kurikulum . ' (' . $k->tahun . ')');
                    $row++;
                }

                $sheet->getColumnDimension('G')->setWidth(3);
                $sheet->getColumnDimension('H')->setAutoSize(true);
                $sheet->getColumnDimension('I')->setAutoSize(true);
            }
        ];
    }
}