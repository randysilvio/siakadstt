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
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class DosensImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    private $dosenRole;

    public function __construct()
    {
        $this->dosenRole = Role::where('name', 'dosen')->first();
    }

    public function model(array $row)
    {
        if (!isset($row['nidn']) || !isset($row['email'])) {
            return null;
        }

        return DB::transaction(function () use ($row) {
            $email = trim($row['email']);
            $nidn = trim((string) $row['nidn']);
            
            // 1. Cek User
            $user = User::where('email', $email)->first();

            if ($user) {
                $dataUpdate = ['name' => $row['nama_lengkap']];
                if (!empty($row['password'])) {
                    $dataUpdate['password'] = Hash::make($row['password']);
                }
                $user->update($dataUpdate);
            } else {
                $user = User::create([
                    'name' => $row['nama_lengkap'],
                    'email' => $email,
                    'password' => Hash::make($row['password']),
                ]);
            }

            if ($this->dosenRole) {
                $user->roles()->syncWithoutDetaching($this->dosenRole->id);
            }

            // 2. Update/Create Profil Dosen
            return Dosen::updateOrCreate(
                ['nidn' => $nidn],
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
        ];
    }
}