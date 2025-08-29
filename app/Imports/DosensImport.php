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

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
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

            // PERBAIKAN: Menambahkan semua data profil baru dari file Excel
            return new Dosen([
               'user_id'             => $user->id,
               'nidn'                => $row['nidn'],
               'nama_lengkap'        => $row['nama_lengkap'],
               'jabatan_akademik'    => $row['jabatan_akademik'] ?? null,
               'bidang_keahlian'     => $row['bidang_keahlian'] ?? null,
               'deskripsi_diri'      => $row['deskripsi_diri'] ?? null,
               'email_institusi'     => $row['email_institusi'] ?? null,
               'link_google_scholar' => $row['link_google_scholar'] ?? null,
               'link_sinta'          => $row['link_sinta'] ?? null,
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
            // PERBAIKAN: Menambahkan aturan validasi untuk kolom baru
            'jabatan_akademik' => 'nullable|string|max:255',
            'bidang_keahlian' => 'nullable|string|max:255',
            'deskripsi_diri' => 'nullable|string',
            'email_institusi' => 'nullable|email|unique:dosens,email_institusi',
            'link_google_scholar' => 'nullable|url',
            'link_sinta' => 'nullable|url',
        ];
    }
}