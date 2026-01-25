<?php

namespace App\Exports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DosensExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    public function collection()
    {
        return Dosen::all();
    }

    public function map($dosen): array
    {
        return [
            $dosen->nidn,
            $dosen->nama_lengkap,
            $dosen->user->email ?? '',
            '', // Password hidden
            "'" . $dosen->nik,
            $dosen->jenis_kelamin,
            $dosen->agama,
            $dosen->status_kepegawaian,
            $dosen->jabatan_akademik,
            $dosen->pangkat_golongan,
            $dosen->bidang_keahlian,
            $dosen->email_institusi,
            $dosen->tempat_lahir,
            $dosen->tanggal_lahir,
            $dosen->alamat,
            "'" . $dosen->nuptk,
            "'" . $dosen->npwp,
            $dosen->no_sk_pengangkatan,
            $dosen->tmt_sk_pengangkatan,
            $dosen->link_google_scholar,
            $dosen->link_sinta,
        ];
    }

    public function headings(): array
    {
        return [
            ['DATA EKSPOR DOSEN'],
            [
                'NIDN', 'Nama Lengkap', 'Email', 'Password', 'NIK', 'Jenis Kelamin', 'Agama',
                'Status Kepegawaian', 'Jabatan Akademik', 'Pangkat Golongan', 'Bidang Keahlian',
                'Email Institusi', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat',
                'NUPTK', 'NPWP', 'No SK Pengangkatan', 'TMT SK Pengangkatan',
                'Link Google Scholar', 'Link Sinta'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
        ];
    }
}