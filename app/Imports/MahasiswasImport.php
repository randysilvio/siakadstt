<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MahasiswasImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            // 1. Buat akun user baru
            $user = User::create([
                'name'     => $row['nama_lengkap'],
                'email'    => $row['email'],
                'password' => Hash::make($row['password']),
                'role'     => 'mahasiswa',
            ]);

            // 2. Buat data mahasiswa baru termasuk biodata
            return new Mahasiswa([
                'user_id'           => $user->id,
                'nim'               => $row['nim'],
                'nama_lengkap'      => $row['nama_lengkap'],
                'program_studi_id'  => $row['program_studi_id'],
                'dosen_wali_id'     => $row['dosen_wali_id'] ?? null,
                'tahun_masuk'       => $row['tahun_masuk'],
                'tempat_lahir'      => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'     => $row['tanggal_lahir'] ?? null,
                'jenis_kelamin'     => $row['jenis_kelamin'] ?? null,
                'alamat'            => $row['alamat'] ?? null,
                'nomor_telepon'     => $row['nomor_telepon'] ?? null,
            ]);
        });
    }

    /**
     * Menambahkan validasi untuk setiap baris di file Excel.
     */
    public function rules(): array
    {
        return [
            'nim' => 'required|unique:mahasiswas,nim',
            'nama_lengkap' => 'required|string',
            'program_studi_id' => 'required|exists:program_studis,id',
            'dosen_wali_id' => 'nullable|exists:dosens,id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:15',
            'tahun_masuk' => 'required|digits:4',
        ];
    }
}
