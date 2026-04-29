<?php

namespace App\Exports;

use App\Models\Camaba;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PmbExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Camaba::with(['user', 'prodi1', 'period'])->orderBy('created_at', 'desc');

        if (!empty($this->filters['status'])) $query->where('status_pendaftaran', $this->filters['status']);
        if (!empty($this->filters['pmb_period_id'])) $query->where('pmb_period_id', $this->filters['pmb_period_id']);
        if (!empty($this->filters['pilihan_prodi_1_id'])) $query->where('pilihan_prodi_1_id', $this->filters['pilihan_prodi_1_id']);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No Pendaftaran', 'Nama Lengkap', 'Jenis Kelamin', 'No WhatsApp',
            'Asal Sekolah', 'Program Studi Pilihan', 'Gelombang', 'Status Seleksi', 'Tanggal Daftar'
        ];
    }

    public function map($camaba): array
    {
        return [
            $camaba->no_pendaftaran,
            $camaba->user->name ?? '',
            $camaba->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan',
            $camaba->no_hp,
            $camaba->sekolah_asal,
            $camaba->prodi1->nama_prodi ?? '',
            $camaba->period->nama_gelombang ?? '',
            strtoupper(str_replace('_', ' ', $camaba->status_pendaftaran)),
            $camaba->created_at->format('d/m/Y')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [ 1 => ['font' => ['bold' => true]] ];
    }
}