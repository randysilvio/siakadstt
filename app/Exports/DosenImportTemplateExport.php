<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DosenImportTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    public function title(): string
    {
        return 'Template Impor Dosen';
    }

    public function collection(): Collection
    {
        // PERBAIKAN: Menyediakan contoh data untuk semua kolom baru
        return new Collection([
            [
                'nidn' => '0912345601',
                'nama_lengkap' => 'Dr. Budi Santoso, M.Kom.',
                'email' => 'budi.santoso@example.com',
                'password' => 'password123',
                'jabatan_akademik' => 'Lektor Kepala',
                'bidang_keahlian' => 'Rekayasa Perangkat Lunak, AI',
                'deskripsi_diri' => 'Dosen senior dengan pengalaman mengajar lebih dari 10 tahun.',
                'email_institusi' => 'budi.s@sttgpipapua.ac.id',
                'link_google_scholar' => 'https://scholar.google.com/citations?user=xxxx',
                'link_sinta' => 'https://sinta.kemdikbud.go.id/authors/profile/xxxx',
            ]
        ]);
    }

    public function headings(): array
    {
        // PERBAIKAN: Menambahkan header kolom baru sesuai urutan di collection
        return [
            'nidn',
            'nama_lengkap',
            'email',
            'password',
            'jabatan_akademik',
            'bidang_keahlian',
            'deskripsi_diri',
            'email_institusi',
            'link_google_scholar',
            'link_sinta',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIDN
            'C' => NumberFormat::FORMAT_TEXT, // email
            'H' => NumberFormat::FORMAT_TEXT, // email_institusi
        ];
    }
}