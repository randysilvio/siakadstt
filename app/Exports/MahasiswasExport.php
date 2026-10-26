<?php

namespace App\Exports;

use App\Models\Mahasiswa;
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

class MahasiswasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $search;
    protected $program_studi_id;

    public function __construct($search = null, $program_studi_id = null)
    {
        $this->search = $search;
        $this->program_studi_id = $program_studi_id;
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
        return Mahasiswa::with('programStudi', 'user', 'dosenWali')
            ->when($this->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->when($this->program_studi_id, function ($query, $id) {
                return $query->where('program_studi_id', $id);
            })
            ->get();
    }

    public function map($mhs): array
    {
        return [
            $this->safeCell($mhs->nim),
            $this->safeCell($mhs->nama_lengkap),
            $this->safeCell($mhs->programStudi->nama_prodi ?? '-'),
            $this->safeCell($mhs->tahun_masuk ?? $mhs->angkatan),
            $this->safeCell($mhs->status_mahasiswa),
            $this->safeCell($mhs->dosenWali->nama_lengkap ?? '-'),
            "'" . $mhs->nik, 
            "'" . $mhs->nisn,
            $this->safeCell($mhs->jalur_pendaftaran),
            $this->safeCell($mhs->user->email ?? '-'),
            "'" . $mhs->nomor_telepon,
            $this->safeCell($mhs->tempat_lahir),
            $mhs->tanggal_lahir ? $mhs->tanggal_lahir->format('Y-m-d') : '-',
            $this->safeCell($mhs->jenis_kelamin),
            $this->safeCell($mhs->agama),
            $this->safeCell($mhs->alamat),
            $this->safeCell($mhs->dusun), 
            $this->safeCell($mhs->rt), 
            $this->safeCell($mhs->rw),
            $this->safeCell($mhs->kelurahan), 
            $this->safeCell($mhs->kecamatan), 
            $this->safeCell($mhs->kode_pos),
            $this->safeCell($mhs->jenis_tinggal), 
            $this->safeCell($mhs->alat_transportasi),
            $this->safeCell($mhs->nama_ibu_kandung),
            "'" . $mhs->nik_ibu,
            $this->safeCell($mhs->pendidikan_ibu), 
            $this->safeCell($mhs->pekerjaan_ibu), 
            $this->safeCell($mhs->penghasilan_ibu),
            $this->safeCell($mhs->nama_ayah),
            "'" . $mhs->nik_ayah,
            $this->safeCell($mhs->pendidikan_ayah), 
            $this->safeCell($mhs->pekerjaan_ayah), 
            $this->safeCell($mhs->penghasilan_ayah),
            $this->safeCell($mhs->nama_wali), 
            $this->safeCell($mhs->pekerjaan_wali)
        ];
    }

    public function headings(): array
    {
        $namaKampus = strtoupper(Pengaturan::get('nama_kampus', 'SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA'));
        $alamatKampus = Pengaturan::get('kop_alamat', 'Jl. Jenderal Sudirman, Fakfak, Papua Barat');
        $istilahPembimbing = Pengaturan::get('istilah_pembimbing', 'Dosen Tutor');

        return [
            [$namaKampus],
            [$alamatKampus],
            ['LAPORAN DATA MAHASISWA'],
            [''],
            [
                'NIM', 'Nama Lengkap', 'Program Studi', 'Angkatan', 'Status Mahasiswa', $istilahPembimbing,
                'NIK', 'NISN', 'Jalur Pendaftaran', 'Email', 'No HP',
                'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'Agama',
                'Alamat', 'Dusun', 'RT', 'RW', 'Kelurahan', 'Kecamatan', 'Kode Pos',
                'Jenis Tinggal', 'Alat Transportasi',
                'Nama Ibu Kandung', 'NIK Ibu', 'Pendidikan Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu',
                'Nama Ayah', 'NIK Ayah', 'Pendidikan Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah',
                'Nama Wali', 'Pekerjaan Wali'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $hexColor = 'FF' . strtoupper(ltrim(Pengaturan::get('warna_primer', '#1e293b'), '#'));
        return [
            5 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $hexColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
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
                $highestColumn = 'AJ';

                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->mergeCells('A3:' . $highestColumn . '3');

                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A5:' . $highestColumn . $highestRow)->applyFromArray($styleArray);
                $sheet->getRowDimension(5)->setRowHeight(25);
            },
        ];
    }
}