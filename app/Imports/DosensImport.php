<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DosensImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Menggunakan transaction agar jika salah satu gagal, semua dibatalkan
        return DB::transaction(function () use ($row) {
            // Cari user berdasarkan email, jika tidak ada, buat baru
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama_lengkap'],
                    'password' => Hash::make($row['password']),
                    'role' => 'dosen',
                ]
            );

            // Buat data dosen baru yang terhubung dengan user
            return new Dosen([
               'user_id'     => $user->id,
               'nidn'        => $row['nidn'],
               'nama_lengkap' => $row['nama_lengkap'],
            ]);
        });
    }

    /**
     * Tentukan aturan validasi untuk setiap baris di Excel.
     */
    public function rules(): array
    {
        return [
            'nidn' => 'required|string|unique:dosens,nidn',
            'nama_lengkap' => 'required|string',
            
            // Cek email di tabel 'users', bukan hanya format email
            'email' => 'required|email|unique:users,email',
            
            'password' => 'required|string|min:8',
        ];
    }
}