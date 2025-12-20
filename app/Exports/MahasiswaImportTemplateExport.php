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
                // Wajib
                'nim' => '2025001',
                'nama_lengkap' => 'Contoh Mahasiswa',
                'program_studi_id' => '1', 
                'dosen_wali_id' => '5',
                'email' => 'mhs@contoh.com',
                'password' => '123456',
                'tahun_masuk' => '2025',
                'status_mahasiswa' => 'Aktif',
                
                // Identitas
                'nik' => '9102xxxxxxxxxxxx',
                'nisn' => '005xxxxxxx',
                'jalur_pendaftaran' => 'Mandiri',
                'tempat_lahir' => 'Jayapura',
                'tanggal_lahir' => '2005-08-17',
                'jenis_kelamin' => 'L',
                'agama' => 'Kristen Protestan',
                'nomor_telepon' => '08123xxxx',

                // Alamat
                'alamat' => 'Jl. Merdeka No 1',
                'dusun' => 'Dusun 1',
                'rt' => '01',
                'rw' => '02',
                'kelurahan' => 'Vim',
                'kecamatan' => 'Abepura',
                'kode_pos' => '99xxx',
                'jenis_tinggal' => 'Bersama Orang Tua',
                'alat_transportasi' => 'Motor',

                // Ortu
                'nama_ibu_kandung' => 'Maria',
                'nik_ibu' => '91xxxxxxxx',
                'nama_ayah' => 'Yosep',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            // Utama
            'nim', 'nama_lengkap', 'program_studi_id', 'dosen_wali_id',
            'email', 'password', 'tahun_masuk', 'status_mahasiswa',
            
            // Identitas
            'nik', 'nisn', 'jalur_pendaftaran', 'tempat_lahir', 'tanggal_lahir', 
            'jenis_kelamin', 'agama', 'nomor_telepon',
            
            // Alamat
            'alamat', 'dusun', 'rt', 'rw', 'kelurahan', 'kecamatan', 
            'kode_pos', 'jenis_tinggal', 'alat_transportasi',

            // Ortu
            'nama_ibu_kandung', 'nik_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
            'nama_ayah', 'nik_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
            'nama_wali', 'pekerjaan_wali',

            '', // Spasi
            'REF: ID PRODI', 'REF: ID DOSEN'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIM
            'I' => NumberFormat::FORMAT_TEXT, // NIK
            'J' => NumberFormat::FORMAT_TEXT, // NISN
            'R' => NumberFormat::FORMAT_TEXT, // RT
            'S' => NumberFormat::FORMAT_TEXT, // RW
            'V' => NumberFormat::FORMAT_TEXT, // Kode Pos
            'X' => NumberFormat::FORMAT_TEXT, // NIK Ibu
            'AB' => NumberFormat::FORMAT_TEXT, // NIK Ayah
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Warnai Header
                $sheet->getStyle('A1:AG1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']],
                ]);
                $sheet->getStyle('AI1:AJ1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF64748B']],
                ]);

                // Referensi Data (Tabel Kanan)
                $prodis = ProgramStudi::all();
                $row = 2;
                foreach ($prodis as $prodi) {
                    $sheet->setCellValue('AI' . $row, $prodi->id . ' - ' . $prodi->nama_prodi);
                    $row++;
                }

                $dosens = Dosen::all();
                $row = 2;
                foreach ($dosens as $dosen) {
                    $sheet->setCellValue('AJ' . $row, $dosen->id . ' - ' . $dosen->nama_lengkap);
                    $row++;
                }

                $sheet->getColumnDimension('AH')->setWidth(2); // Spasi
                $sheet->getColumnDimension('AI')->setAutoSize(true);
                $sheet->getColumnDimension('AJ')->setAutoSize(true);
            },
        ];
    }
}