<?php

namespace App\Exports;

use App\Models\Dosen;
use App\Models\ProgramStudi;
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
                'kode_mk' => 'MK101',
                'nama_mata_kuliah' => 'Pengantar Teologi',
                'sks' => '2',
                'semester' => '1',
                'program_studi' => '1', // Bisa isi ID (1) atau Nama (Teologi)
                'dosen_pengampu' => '12345678', // Bisa NIDN atau Nama Dosen
                'kurikulum_id' => '1',
                'deskripsi' => 'Mata kuliah dasar...',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            ['TEMPLATE IMPOR DATA MATA KULIAH (JANGAN HAPUS BARIS INI)'],
            [
                'Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 
                'Program Studi', 'Dosen Pengampu', 'Kurikulum ID', 'Deskripsi',
                '', // Spasi
                'REF: ID PRODI', 'REF: NIDN DOSEN' // Kamus Data
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Kode MK
            'F' => NumberFormat::FORMAT_TEXT, // NIDN Dosen (biar 0 depan tidak hilang)
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Style Header Utama
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']], 'alignment' => ['horizontal' => 'center']]);
                $sheet->getStyle('A2:H2')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF64748B']]]);

                // Style Referensi (Kanan)
                $sheet->getStyle('J2:K2')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFEF4444']]]);

                // REF DATA: PRODI
                $prodis = ProgramStudi::all();
                $row = 3;
                foreach($prodis as $p) {
                    $sheet->setCellValue('J' . $row, $p->id . ' - ' . $p->nama_prodi);
                    $row++;
                }

                // REF DATA: DOSEN
                $dosens = Dosen::select('nidn', 'nama_lengkap')->get();
                $row = 3;
                foreach($dosens as $d) {
                    $sheet->setCellValueExplicit('K' . $row, $d->nidn . ' - ' . $d->nama_lengkap, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $row++;
                }
                
                foreach(range('A','K') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
            },
        ];
    }
}