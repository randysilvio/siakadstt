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
    public function title(): string { return 'Template Mahasiswa'; }
    
    public function collection(): Collection
    {
        return new Collection([
            [
                'nim' => '202401001',
                'nama_lengkap' => 'Contoh Mahasiswa',
                'program_studi_id' => '1', // WAJIB ISI ANGKA ID (LIHAT TABEL KANAN)
                'dosen_wali_id' => '5',
                'email' => 'mhs@contoh.com',
                'password' => 'password123',
                'tempat_lahir' => 'Jayapura',
                'tanggal_lahir' => '2005-08-17',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Merdeka No. 1',
                'nomor_telepon' => '08123456789',
                'tahun_masuk' => '2024',
                'status_mahasiswa' => 'Aktif',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'nim', 'nama_lengkap', 'program_studi_id', 'dosen_wali_id',
            'email', 'password', 'tempat_lahir', 'tanggal_lahir',
            'jenis_kelamin', 'alamat', 'nomor_telepon', 'tahun_masuk', 'status_mahasiswa',
            '', // Spasi
            'REF: ID PRODI (Wajib Diisi)', 'REF: ID DOSEN WALI'
        ];
    }

    public function columnFormats(): array
    {
        return [ 'A' => NumberFormat::FORMAT_TEXT, 'K' => NumberFormat::FORMAT_TEXT ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                // Header Styling
                $sheet->getStyle('A1:M1')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']]]);
                $sheet->getStyle('O1:P1')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF475569']]]);

                // GENERATE TABEL BANTUAN
                $prodis = ProgramStudi::select('id', 'nama_prodi')->get();
                $row = 2;
                foreach ($prodis as $prodi) {
                    $sheet->setCellValue('O' . $row, $prodi->id . ' - ' . $prodi->nama_prodi);
                    $row++;
                }

                $dosens = Dosen::select('id', 'nama_lengkap')->get();
                $row = 2;
                foreach ($dosens as $dosen) {
                    $sheet->setCellValue('P' . $row, $dosen->id . ' - ' . $dosen->nama_lengkap);
                    $row++;
                }
                
                $sheet->getColumnDimension('N')->setWidth(3);
                $sheet->getColumnDimension('O')->setAutoSize(true);
                $sheet->getColumnDimension('P')->setAutoSize(true);
            },
        ];
    }
}