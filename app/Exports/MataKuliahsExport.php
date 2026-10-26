<?php

namespace App\Exports;

use App\Models\MataKuliah;
use App\Models\Pengaturan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MataKuliahsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    private function safeCell($value): string
    {
        $str = (string) ($value ?? '');
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $str;
        }
        return $str;
    }

    public function collection()
    {
        return MataKuliah::with(['dosen', 'programStudi'])->get();
    }

    public function map($mk): array
    {
        return [
            $this->safeCell($mk->kode_mk),
            $this->safeCell($mk->nama_mk),
            $mk->sks,
            $mk->semester,
            $this->safeCell($mk->programStudi->nama_prodi ?? '-'),
            $this->safeCell($mk->dosen->nama_lengkap ?? '-'),
            $mk->kurikulum_id,
            $this->safeCell($mk->deskripsi ?? '-'),
        ];
    }

    public function headings(): array
    {
        $namaKampus = strtoupper(Pengaturan::get('nama_kampus', 'SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA'));
        $alamatKampus = Pengaturan::get('kop_alamat', 'Jl. Jenderal Sudirman, Fakfak, Papua Barat');

        return [
            [$namaKampus],
            [$alamatKampus],
            ['LAPORAN DATA MATA KULIAH'],
            [''],
            ['Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Program Studi', 'Dosen Pengampu', 'Kurikulum ID', 'Deskripsi']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $hexColor = 'FF' . strtoupper(ltrim(Pengaturan::themeColors()['primary'] ?? '#1e293b', '#'));
        return [
            5 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $hexColor]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]],
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true, 'size' => 13, 'underline' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestColumn = 'H'; 

                $sheet->mergeCells('A1:' . $highestColumn . '1'); 
                $sheet->mergeCells('A2:' . $highestColumn . '2'); 
                $sheet->mergeCells('A3:' . $highestColumn . '3'); 

                $styleArray = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
                $sheet->getStyle('A5:' . $highestColumn . $highestRow)->applyFromArray($styleArray);
                $sheet->getRowDimension(5)->setRowHeight(25);
            },
        ];
    }
}