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

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Mahasiswa::with('programStudi', 'user', 'dosenWali')
            ->when($this->search, function ($query, $search) {
                return $query->where('nama_lengkap', 'like', "%{$search}%")
                             ->orWhere('nim', 'like', "%{$search}%");
            })
            ->when($this->program_studi_id, function ($query, $program_studi_id) {
                return $query->where('program_studi_id', $program_studi_id);
            })
            ->get();
    }

    /**
     * Menentukan judul kolom di file Excel.
     */
    public function headings(): array
    {
        return [
            'NIM',
            'Nama Lengkap',
            'Program Studi',
            'Tahun Masuk',
            'Dosen Wali',
            'Email Akun',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Alamat',
            'Nomor Telepon',
        ];
    }

    /**
     * Memetakan data dari collection ke setiap baris di Excel.
     */
    public function map($mahasiswa): array
    {
        return [
            $mahasiswa->nim,
            $mahasiswa->nama_lengkap,
            $mahasiswa->programStudi->nama_prodi,
            $mahasiswa->tahun_masuk,
            optional($mahasiswa->dosenWali)->nama_lengkap,
            optional($mahasiswa->user)->email,
            $mahasiswa->tempat_lahir,
            $mahasiswa->tanggal_lahir,
            $mahasiswa->jenis_kelamin,
            $mahasiswa->alamat,
            $mahasiswa->nomor_telepon,
        ];
    }
}
