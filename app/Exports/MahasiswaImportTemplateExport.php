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
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MahasiswaImportTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    public function title(): string
    {
        return 'Template Mahasiswa';
    }
    
    public function collection(): Collection
    {
        return new Collection([[
            'nim' => '2025001', 'nama_lengkap' => 'Contoh Mahasiswa', 'program_studi' => '1', 'angkatan' => '2025',
            'status_mahasiswa' => 'Aktif', 'dosen_wali' => '5', 'nik' => '9102xxxxxxxxxxxx', 'nisn' => '005xxxxxxx',
            'jalur_pendaftaran' => 'Mandiri', 'email' => 'mhs@contoh.com', 'password' => '123456', 'no_hp' => '08123xxxx',
            'tempat_lahir' => 'Jayapura', 'tanggal_lahir' => '2005-08-17', 'jenis_kelamin' => 'L', 'agama' => 'Kristen Protestan',
            'alamat' => 'Jl. Merdeka No 1', 'dusun' => 'Dusun 1', 'rt' => '01', 'rw' => '02', 'kelurahan' => 'Vim',
            'kecamatan' => 'Abepura', 'kode_pos' => '99xxx', 'jenis_tinggal' => 'Bersama Orang Tua', 'alat_transportasi' => 'Motor',
            'nama_ibu_kandung' => 'Maria', 'nik_ibu' => '91xxxxxxxx', 'pendidikan_ibu' => 'SMA', 'pekerjaan_ibu' => 'Ibu Rumah Tangga',
            'penghasilan_ibu' => '0', 'nama_ayah' => 'Yosep', 'nik_ayah' => '91xxxxxxxx', 'pendidikan_ayah' => 'S1',
            'pekerjaan_ayah' => 'PNS', 'penghasilan_ayah' => '5000000', 'nama_wali' => '', 'pekerjaan_wali' => '',
        ]]);
    }

    public function headings(): array
    {
        return [
            ['TEMPLATE IMPOR DATA MAHASISWA (JANGAN HAPUS BARIS INI)'],
            [
                'NIM', 'Nama Lengkap', 'Program Studi', 'Angkatan', 'Status Mahasiswa', 'Dosen Wali',
                'NIK', 'NISN', 'Jalur Pendaftaran', 'Email', 'Password', 'No HP', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'Agama',
                'Alamat', 'Dusun', 'RT', 'RW', 'Kelurahan', 'Kecamatan', 'Kode Pos', 'Jenis Tinggal', 'Alat Transportasi',
                'Nama Ibu Kandung', 'NIK Ibu', 'Pendidikan Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu',
                'Nama Ayah', 'NIK Ayah', 'Pendidikan Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah',
                'Nama Wali', 'Pekerjaan Wali', '', 'REF: ID PRODI', 'REF: ID DOSEN'
            ]
        ];
    }

    public function columnFormats(): array
    {
        return ['A' => NumberFormat::FORMAT_TEXT, 'G' => NumberFormat::FORMAT_TEXT, 'H' => NumberFormat::FORMAT_TEXT, 'L' => NumberFormat::FORMAT_TEXT, 'N' => NumberFormat::FORMAT_DATE_YYYYMMDD];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $sheet->mergeCells('A1:AK1');
                $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0d9488']], 'alignment' => ['horizontal' => 'center']]);
                $sheet->getStyle('A2:AL2')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF64748B']]]);
                $sheet->getStyle('AM2:AN2')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEF4444']]]);

                $prodis = ProgramStudi::all(); $row = 3;
                foreach ($prodis as $prodi) { $sheet->setCellValue('AM' . $row, $prodi->id . ' - ' . $prodi->nama_prodi); $row++; }

                $dosens = Dosen::select('id', 'nama_lengkap')->get(); $row = 3;
                foreach ($dosens as $dosen) { $sheet->setCellValue('AN' . $row, $dosen->id . ' - ' . $dosen->nama_lengkap); $row++; }

                foreach(range('A','AN') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
            },
        ];
    }
}