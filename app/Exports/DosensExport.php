<?php

namespace App\Exports;

use App\Models\Dosen;
use App\Models\Pengaturan;
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
    private function safeCell($value): string
    {
        $str = (string) ($value ?? '');
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $str;
        }
        return $str;
    }

    public function collection()
    {
        return Dosen::with(['user', 'programStudi'])->get();
    }

    public function map($dosen): array
    {
        return [
            "'" . $dosen->nidn, 
            $this->safeCell($dosen->nama_lengkap), 
            $this->safeCell($dosen->user->email ?? ''),
            $this->safeCell($dosen->programStudi->nama_prodi ?? 'Tanpa Prodi'),
            "'" . $dosen->nik, 
            $this->safeCell($dosen->jenis_kelamin), 
            $this->safeCell($dosen->agama),
            $this->safeCell($dosen->status_kepegawaian), 
            $this->safeCell($dosen->jabatan_akademik), 
            $this->safeCell($dosen->pangkat_golongan),
            $this->safeCell($dosen->bidang_keahlian), 
            $this->safeCell($dosen->email_institusi), 
            $this->safeCell($dosen->tempat_lahir),
            $dosen->tanggal_lahir, 
            $this->safeCell($dosen->alamat), 
            "'" . $dosen->nuptk,
            "'" . $dosen->npwp, 
            $this->safeCell($dosen->no_sk_pengangkatan), 
            $dosen->tmt_sk_pengangkatan,
            $this->safeCell($dosen->link_google_scholar), 
            $this->safeCell($dosen->link_sinta),
        ];
    }

    public function headings(): array
    {
        $namaKampus = strtoupper(Pengaturan::get('nama_kampus', 'SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA'));
        $alamatKampus = Pengaturan::get('kop_alamat', 'Jl. Jenderal Sudirman, Fakfak, Papua Barat');

        return [
            [$namaKampus],
            [$alamatKampus],
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
        $hexColor = 'FF' . strtoupper(ltrim(Pengaturan::themeColors()['primary'] ?? '#1e293b', '#'));
        return [
            5 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $hexColor]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]],
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true, 'size' => 13, 'underline' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestColumn = 'U';

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