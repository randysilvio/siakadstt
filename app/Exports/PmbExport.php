<?php

namespace App\Exports;

use App\Models\Camaba;
use App\Models\Pengaturan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PmbExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

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
        $query = Camaba::with(['user', 'prodi1', 'period'])->orderBy('created_at', 'desc');

        if (!empty($this->filters['status'])) $query->where('status_pendaftaran', $this->filters['status']);
        if (!empty($this->filters['pmb_period_id'])) $query->where('pmb_period_id', $this->filters['pmb_period_id']);
        if (!empty($this->filters['pilihan_prodi_1_id'])) $query->where('pilihan_prodi_1_id', $this->filters['pilihan_prodi_1_id']);

        return $query->get();
    }

    public function map($camaba): array
    {
        return [
            $this->safeCell($camaba->no_pendaftaran),
            $this->safeCell($camaba->user->name ?? ''),
            $camaba->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan',
            "'" . $camaba->no_hp,
            $this->safeCell($camaba->sekolah_asal),
            $this->safeCell($camaba->prodi1->nama_prodi ?? ''),
            $this->safeCell($camaba->period->nama_gelombang ?? ''),
            strtoupper(str_replace('_', ' ', $camaba->status_pendaftaran)),
            $camaba->created_at ? $camaba->created_at->format('d/m/Y') : '-'
        ];
    }

    public function headings(): array
    {
        $singkatanPt = strtoupper(Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA'));
        $alamatKampus = Pengaturan::get('kop_alamat', 'Jl. Jenderal Sudirman, Fakfak, Papua Barat');

        return [
            ['PANITIA PENERIMAAN MAHASISWA BARU (PMB) ' . $singkatanPt],
            [$alamatKampus],
            ['REKAPITULASI PENDAFTAR'],
            [''],
            ['No Pendaftaran', 'Nama Lengkap', 'Jenis Kelamin', 'No WhatsApp', 'Asal Sekolah', 'Program Studi Pilihan', 'Gelombang', 'Status Seleksi', 'Tanggal Daftar']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $hexColor = 'FF' . strtoupper(ltrim(Pengaturan::get('warna_primer', '#1e293b'), '#'));
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
                $highestColumn = 'I'; 

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