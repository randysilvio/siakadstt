<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',             'display_name' => 'Administrator'],
            ['name' => 'dosen',             'display_name' => 'Dosen'],
            ['name' => 'mahasiswa',         'display_name' => 'Mahasiswa'],
            ['name' => 'camaba',            'display_name' => 'Calon Mahasiswa Baru (PMB)'],
            ['name' => 'kaprodi',           'display_name' => 'Ketua Program Studi'],
            ['name' => 'keuangan',          'display_name' => 'Staf Keuangan'],
            ['name' => 'pustakawan',        'display_name' => 'Pustakawan'],
            ['name' => 'rektorat',          'display_name' => 'Pimpinan / Rektorat'],
            ['name' => 'penjaminan_mutu',   'display_name' => 'Penjaminan Mutu'],
            ['name' => 'administrasi_umum', 'display_name' => 'Administrasi Umum & Sarpras'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}