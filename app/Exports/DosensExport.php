<?php

namespace App\Exports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class DosensExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    public function collection()
    {
        return Dosen::all();
    }

    public function map($dosen): array
    {
        return [
            $dosen->nidn,
            "'" . $dosen->nik, // Format text agar tidak error ilmiah
            $dosen->nama_lengkap,
            $dosen->jenis_kelamin,
            $dosen->status_kepegawaian,
            $dosen->jabatan_akademik,
            $dosen->pangkat_golongan,
            $dosen->bidang_keahlian,
            $dosen->email_institusi,
            $dosen->nuptk,
            $dosen->npwp,
        ];
    }

    public function headings(): array
    {
        return [
            'NIDN', 'NIK', 'Nama Lengkap', 'L/P', 'Status Pegawai',
            'Jabatan Akademik', 'Pangkat', 'Bidang Keahlian',
            'Email Institusi', 'NUPTK', 'NPWP'
        ];
    }
}