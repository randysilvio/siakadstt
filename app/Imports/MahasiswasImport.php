<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class MahasiswasImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            // Cari atau buat user baru
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama_lengkap'],
                    'password' => Hash::make($row['password']),
                    'role' => 'mahasiswa',
                ]
            );

            // Buat data mahasiswa dengan semua kolom dari template
            return new Mahasiswa([
                'user_id'           => $user->id,
                'nim'               => $row['nim'],
                'nama_lengkap'      => $row['nama_lengkap'],
                'program_studi_id'  => $row['program_studi_id'],
                'dosen_wali_id'     => $row['dosen_wali_id'],
                'tahun_masuk'       => $row['tahun_masuk'],
                'tempat_lahir'      => $row['tempat_lahir'],
                'tanggal_lahir'     => !empty($row['tanggal_lahir']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_lahir'])->format('Y-m-d') : null,
                'jenis_kelamin'     => $row['jenis_kelamin'],
                'alamat'            => $row['alamat'],
                'nomor_telepon'     => $row['nomor_telepon'],
                'status_mahasiswa'  => 'Aktif', // Status default saat impor
            ]);
        });
    }

    public function rules(): array
    {
        return [
            'nim'               => 'required|string|unique:mahasiswas,nim',
            'nama_lengkap'      => 'required|string|max:255',
            'program_studi_id'  => 'required|integer|exists:program_studis,id',
            // PERBAIKAN: Menambahkan validasi unique di tabel users
            'email'             => 'required|email|unique:users,email', 
            'password'          => 'required|string|min:8',
            'dosen_wali_id'     => 'nullable|integer|exists:dosens,id',
            'tahun_masuk'       => 'required|digits:4|integer',
            'tempat_lahir'      => 'nullable|string',
            'tanggal_lahir'     => 'nullable|numeric', // Excel mengirim tanggal sebagai angka
            'jenis_kelamin'     => 'nullable|in:L,P',
            'alamat'            => 'nullable|string',
            'nomor_telepon'     => 'nullable|string',
        ];
    }
}