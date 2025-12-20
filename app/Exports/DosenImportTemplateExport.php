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
                'nik' => '917101xxxxxxxx',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => 'Dosen Tetap',
                'jabatan_akademik' => 'Lektor Kepala',
                'pangkat_golongan' => 'III/c',
                'bidang_keahlian' => 'Rekayasa Perangkat Lunak',
                'email_institusi' => 'budi@stt.ac.id',
                'tempat_lahir' => 'Jayapura',
                'tanggal_lahir' => '1980-05-20',
                'alamat' => 'Jl. Sentani No. 10',
                'nuptk' => '1234xxxx',
                'npwp' => '1234xxxx',
                'no_sk_pengangkatan' => 'SK-2024/001',
                'tmt_sk_pengangkatan' => '2024-01-01',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'nidn', 'nama_lengkap', 'email', 'password', 'nik', 'jenis_kelamin',
            'status_kepegawaian', 'jabatan_akademik', 'pangkat_golongan', 'bidang_keahlian',
            'email_institusi', 'tempat_lahir', 'tanggal_lahir', 'alamat',
            'nuptk', 'npwp', 'no_sk_pengangkatan', 'tmt_sk_pengangkatan',
            'link_google_scholar', 'link_sinta',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIDN
            'E' => NumberFormat::FORMAT_TEXT, // NIK
            'O' => NumberFormat::FORMAT_TEXT, // NUPTK
            'P' => NumberFormat::FORMAT_TEXT, // NPWP
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $sheet->getStyle('A1:T1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0d9488']],
                ]);
            },
        ];
    }
}