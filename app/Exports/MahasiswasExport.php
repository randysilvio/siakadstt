<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MahasiswasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;
    protected $program_studi_id;

    public function __construct($search, $program_studi_id)
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
            ->when($this->program_studi_id, function ($query, $program_studi_id) {
                return $query->where('program_studi_id', $program_studi_id);
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'NIM', 'Nama Lengkap', 'Program Studi', 'Angkatan', 'Status', 'Dosen Wali',
            'NIK (KTP)', 'NISN', 'Jalur Daftar', 'Email', 'No HP',
            'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'Agama',
            'Alamat', 'Dusun', 'RT', 'RW', 'Kelurahan', 'Kecamatan', 'Kode Pos',
            'Nama Ibu', 'NIK Ibu', 'Pendidikan Ibu', 'Pekerjaan Ibu',
            'Nama Ayah', 'NIK Ayah', 'Pendidikan Ayah', 'Pekerjaan Ayah',
        ];
    }

    public function map($mhs): array
    {
        return [
            $mhs->nim,
            $mhs->nama_lengkap,
            $mhs->programStudi->nama_prodi,
            $mhs->tahun_masuk,
            $mhs->status_mahasiswa,
            optional($mhs->dosenWali)->nama_lengkap,
            // Identitas
            "'" . $mhs->nik, // Kasih tanda petik agar Excel membacanya sebagai teks (tidak diubah jadi ilmiah)
            "'" . $mhs->nisn,
            $mhs->jalur_pendaftaran,
            optional($mhs->user)->email,
            $mhs->nomor_telepon,
            // Biodata
            $mhs->tempat_lahir,
            $mhs->tanggal_lahir,
            $mhs->jenis_kelamin,
            $mhs->agama,
            // Alamat
            $mhs->alamat,
            $mhs->dusun,
            $mhs->rt, $mhs->rw,
            $mhs->kelurahan, $mhs->kecamatan, $mhs->kode_pos,
            // Ortu
            $mhs->nama_ibu_kandung,
            "'" . $mhs->nik_ibu,
            $mhs->pendidikan_ibu,
            $mhs->pekerjaan_ibu,
            $mhs->nama_ayah,
            "'" . $mhs->nik_ayah,
            $mhs->pendidikan_ayah,
            $mhs->pekerjaan_ayah,
        ];
    }
}