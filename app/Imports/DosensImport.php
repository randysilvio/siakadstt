<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\User;
use App\Models\Role;
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
        $this->dosenRole = Role::where('name', 'dosen')->first();
    }

    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            // 1. Create/Get User
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama_lengkap'],
                    'password' => Hash::make($row['password']),
                ]
            );

            if ($this->dosenRole) {
                $user->roles()->syncWithoutDetaching($this->dosenRole->id);
            }

            // 2. Create/Update Dosen Profile
            // Trim NIDN agar bersih dari spasi
            $nidn = trim((string) $row['nidn']);

            return Dosen::updateOrCreate(
                ['nidn' => $nidn], // Kunci pencarian
                [
                    'user_id'             => $user->id,
                    'nama_lengkap'        => $row['nama_lengkap'],
                    'jabatan_akademik'    => $row['jabatan_akademik'] ?? null,
                    'bidang_keahlian'     => $row['bidang_keahlian'] ?? null,
                    'deskripsi_diri'      => $row['deskripsi_diri'] ?? null,
                    'email_institusi'     => $row['email_institusi'] ?? null,
                    'link_google_scholar' => $row['link_google_scholar'] ?? null,
                    'link_sinta'          => $row['link_sinta'] ?? null,
                ]
            );
        });
    }

    public function rules(): array
    {
        return [
            'nidn' => 'required',
            'nama_lengkap' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];
    }
}