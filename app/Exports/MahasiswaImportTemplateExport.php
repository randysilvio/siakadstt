<?php

namespace App\Exports;

use App\Models\ProgramStudi;
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

class MahasiswaImportTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    public function title(): string
    {
        return 'Template Mahasiswa';
    }
    
    public function collection(): Collection
    {
        return new Collection([
            [
                'nim' => '202401001',
                'nama_lengkap' => 'Contoh Mahasiswa',
                'program_studi_id' => '1', 
                'dosen_wali_id' => '1',
                'email' => 'mhs@contoh.com',
                'password' => 'password123',
                'tempat_lahir' => 'Fakfak',
                'tanggal_lahir' => '2005-08-17',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Contoh No. 1',
                'nomor_telepon' => '081234567890',
                'tahun_masuk' => '2024',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'nim', 'nama_lengkap', 'program_studi_id', 'dosen_wali_id',
            'email', 'password', 'tempat_lahir', 'tanggal_lahir',
            'jenis_kelamin', 'alamat', 'nomor_telepon', 'tahun_masuk',
            '', // Spasi M
            'REFERENSI PRODI (ID - NAMA)', // N
            'REFERENSI DOSEN (ID - NAMA)', // O
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIM Text
            'H' => NumberFormat::FORMAT_TEXT, // Tgl Lahir Text
            'K' => NumberFormat::FORMAT_TEXT, // No Telp Text
            'L' => NumberFormat::FORMAT_TEXT, // Tahun Masuk Text
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Style Header Utama (Teal)
                $sheet->getStyle('A1:L1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']],
                ]);

                // Style Header Referensi (Abu)
                $sheet->getStyle('N1:O1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF64748B']],
                ]);

                // DATA REFERENSI
                $prodis = ProgramStudi::all();
                $row = 2;
                foreach ($prodis as $prodi) {
                    $sheet->setCellValue('N' . $row, $prodi->id . ' - ' . $prodi->nama_prodi);
                    $row++;
                }

                $dosens = Dosen::all();
                $row = 2;
                foreach ($dosens as $dosen) {
                    $sheet->setCellValue('O' . $row, $dosen->id . ' - ' . $dosen->nama_lengkap);
                    $row++;
                }

                $sheet->getColumnDimension('M')->setWidth(2);
                $sheet->getColumnDimension('N')->setAutoSize(true);
                $sheet->getColumnDimension('O')->setAutoSize(true);
            },
        ];
    }
}