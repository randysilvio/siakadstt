<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\User;
use App\Models\Role; // <-- 1. Tambahkan model Role
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DosensImport implements ToModel, WithHeadingRow, WithValidation
{
    private $dosenRole;

    public function __construct()
    {
        // Ambil peran 'dosen' sekali saja untuk efisiensi
        $this->dosenRole = Role::where('name', 'dosen')->first();
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            // Cari user berdasarkan email, jika tidak ada, buat baru
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama_lengkap'],
                    'password' => Hash::make($row['password']),
                    // 'role' => 'dosen', <-- 2. Hapus baris ini
                ]
            );

            // =================================================================
            // ===== PERBAIKAN: Menetapkan peran 'dosen' secara otomatis =====
            // =================================================================
            // 3. Lampirkan peran 'dosen' ke pengguna
            if ($this->dosenRole) {
                $user->roles()->syncWithoutDetaching($this->dosenRole->id);
            }
            // =================================================================

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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ];
    }
}
