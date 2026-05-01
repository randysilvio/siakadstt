<?php

namespace App\Exports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DosensExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles, WithEvents
{
    public function collection()
    {
        // Load relasi user dan programStudi agar export lebih cepat (Eager Loading)
        return Dosen::with(['user', 'programStudi'])->get();
    }

    public function map($dosen): array
    {
        return [
            $dosen->nidn, 
            $dosen->nama_lengkap, 
            $dosen->user->email ?? '',
            $dosen->programStudi->nama_prodi ?? 'Tanpa Prodi', // <-- Tambahkan Relasi Prodi
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
            ['SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA'],
            ['Jl. Jenderal Sudirman, Fakfak, Papua Barat'],
            ['LAPORAN DATA DOSEN'],
            [''],
            [
                'NIDN', 'Nama Lengkap', 'Email Login', 'Program Studi', 'NIK', 'Jenis Kelamin', 'Agama',
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
            5 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0d9488']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]],
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true, 'size' => 14, 'underline' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestColumn = 'U'; // <-- Diganti ke 'U' karena nambah 1 kolom (Program Studi)

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