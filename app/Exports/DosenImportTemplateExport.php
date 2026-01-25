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
        return 'Template Dosen';
    }

    public function collection(): Collection
    {
        return new Collection([
            [
                'nidn' => '0012345678',
                'nama_lengkap' => 'Dr. Contoh Dosen, M.Th.',
                'email' => 'dosen@contoh.com',
                'password' => '123456',
                'nik' => '9102xxxxxxxxxxxx',
                'jenis_kelamin' => 'L',
                'agama' => 'Kristen Protestan',
                'status_kepegawaian' => 'Dosen Tetap',
                'jabatan_akademik' => 'Lektor',
                'pangkat_golongan' => 'III/c',
                'bidang_keahlian' => 'Teologi Sistematika',
                'email_institusi' => 'dosen@sttgpipapua.ac.id',
                'tempat_lahir' => 'Fakfak',
                'tanggal_lahir' => '1980-01-01',
                'alamat' => 'Jl. A. Yani',
                'nuptk' => '123xxx',
                'npwp' => '123xxx',
                'no_sk_pengangkatan' => 'SK-001/YAPEL',
                'tmt_sk_pengangkatan' => '2020-01-01',
                'link_google_scholar' => '',
                'link_sinta' => '',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            ['TEMPLATE IMPOR DATA DOSEN (JANGAN HAPUS BARIS INI)'], // Baris 1
            [
                'NIDN', 'Nama Lengkap', 'Email', 'Password', 'NIK', 'Jenis Kelamin', 'Agama',
                'Status Kepegawaian', 'Jabatan Akademik', 'Pangkat Golongan', 'Bidang Keahlian',
                'Email Institusi', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat',
                'NUPTK', 'NPWP', 'No SK Pengangkatan', 'TMT SK Pengangkatan',
                'Link Google Scholar', 'Link Sinta',
                '', // Spasi
                'REF: JABATAN', 'REF: PANGKAT' // Header Referensi
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIDN
            'E' => NumberFormat::FORMAT_TEXT, // NIK
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Style Header Utama
                $sheet->mergeCells('A1:U1');
                $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']], 'alignment' => ['horizontal' => 'center']]);
                $sheet->getStyle('A2:U2')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF64748B']]]);

                // Style Referensi (Kanan)
                $sheet->getStyle('W2:X2')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFEF4444']]]);

                // Isi Referensi Statis (Bantuan User)
                $jabatan = ['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'];
                $pangkat = ['III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b'];
                
                $row = 3;
                foreach($jabatan as $j) { $sheet->setCellValue('W'.$row, $j); $row++; }
                
                $row = 3;
                foreach($pangkat as $p) { $sheet->setCellValue('X'.$row, $p); $row++; }

                foreach(range('A','X') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
            },
        ];
    }
}