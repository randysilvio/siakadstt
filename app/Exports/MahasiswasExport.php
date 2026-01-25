<?php

namespace App\Exports;

use App\Models\Mahasiswa;
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

    public function collection()
    {
        return Mahasiswa::with('programStudi', 'user', 'dosenWali')
            ->when($this->search, function ($query, $search) {
                return $query->where('nama_lengkap', 'like', "%{$search}%")
                             ->orWhere('nim', 'like', "%{$search}%")
                             ->orWhere('nik', 'like', "%{$search}%");
            })
            ->when($this->program_studi_id, function ($query, $id) {
                return $query->where('program_studi_id', $id);
            })
            ->get();
    }

    // === FORMAT DATA YANG AKAN DI-EXPORT ===
    public function map($mhs): array
    {
        return [
            $mhs->nim,
            $mhs->nama_lengkap,
            $mhs->programStudi->nama_prodi ?? '-',
            $mhs->angkatan,
            $mhs->status_mahasiswa,
            $mhs->dosenWali->nama_lengkap ?? '-',
            
            // Identitas (Pakai tanda kutip agar Excel membacanya sebagai teks, bukan ilmiah)
            "'" . $mhs->nik, 
            "'" . $mhs->nisn,
            $mhs->jalur_pendaftaran,
            $mhs->user->email ?? '-',
            
            // Kontak & Biodata
            "'" . $mhs->nomor_telepon,
            $mhs->tempat_lahir,
            $mhs->tanggal_lahir, // Format YYYY-MM-DD
            $mhs->jenis_kelamin,
            $mhs->agama,
            
            // Alamat Lengkap
            $mhs->alamat,
            $mhs->dusun, 
            $mhs->rt, 
            $mhs->rw,
            $mhs->kelurahan, 
            $mhs->kecamatan, 
            $mhs->kode_pos,
            $mhs->jenis_tinggal, 
            $mhs->alat_transportasi,
            
            // Data Ibu
            $mhs->nama_ibu_kandung,
            "'" . $mhs->nik_ibu,
            $mhs->pendidikan_ibu, 
            $mhs->pekerjaan_ibu, 
            $mhs->penghasilan_ibu,
            
            // Data Ayah
            $mhs->nama_ayah,
            "'" . $mhs->nik_ayah,
            $mhs->pendidikan_ayah, 
            $mhs->pekerjaan_ayah, 
            $mhs->penghasilan_ayah,
            
            // Wali
            $mhs->nama_wali, 
            $mhs->pekerjaan_wali
        ];
    }

    // === HEADER EXCEL (KOP SURAT + JUDUL KOLOM) ===
    public function headings(): array
    {
        return [
            ['SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA'], // Baris 1: Nama Kampus
            ['Jl. Ahmad Yani, Fakfak, Papua Barat'],    // Baris 2: Alamat (Sesuaikan jika perlu)
            ['LAPORAN DATA MAHASISWA'],                 // Baris 3: Judul Laporan
            [''],                                       // Baris 4: Spasi Kosong
            [                                           // Baris 5: Header Kolom
                'NIM', 'Nama Lengkap', 'Program Studi', 'Angkatan', 'Status Mahasiswa', 'Dosen Wali',
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

    // === STYLING & FORMATTING ===
    public function styles(Worksheet $sheet)
    {
        return [
            // Style Header Kolom (Baris 5)
            5 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0d9488']], // Warna Hijau Teal
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Style Kop Surat (Baris 1 - Judul Besar)
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Style Kop Surat (Baris 2 - Alamat)
            2 => [
                'font' => ['size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Style Kop Surat (Baris 3 - Judul Laporan)
            3 => [
                'font' => ['bold' => true, 'size' => 14, 'underline' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    // === EVENTS UNTUK MERGE CELL & BORDER ===
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestColumn = 'AJ'; // Kolom terakhir (sesuaikan dengan jumlah kolom headings)

                // 1. Merge Cells untuk Kop Surat (Rata Tengah Sepanjang Tabel)
                $sheet->mergeCells('A1:' . $highestColumn . '1'); // Nama Kampus
                $sheet->mergeCells('A2:' . $highestColumn . '2'); // Alamat
                $sheet->mergeCells('A3:' . $highestColumn . '3'); // Judul Laporan

                // 2. Tambahkan Border untuk Seluruh Data (Mulai Baris 5 sampai akhir)
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A5:' . $highestColumn . $highestRow)->applyFromArray($styleArray);
                
                // 3. Set Tinggi Baris Header agar lega
                $sheet->getRowDimension(5)->setRowHeight(25);
            },
        ];
    }
}