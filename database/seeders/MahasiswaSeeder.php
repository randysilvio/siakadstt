<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = ProgramStudi::first();
        $mahasiswaRole = Role::where('name', 'mahasiswa')->first();

        if ($prodi && $mahasiswaRole) {
            DB::transaction(function () use ($prodi, $mahasiswaRole) {
                // Mahasiswa 1
                $user1 = User::create([
                    'name' => 'Valeria Lesilolo',
                    'email' => 'valeria@sak.com',
                    'password' => Hash::make('password'),
                ]);
                $user1->roles()->attach($mahasiswaRole);
                Mahasiswa::create([
                    'user_id' => $user1->id,
                    'nim' => '672021001',
                    'nama_lengkap' => 'Valeria Lesilolo',
                    'program_studi_id' => $prodi->id,
                ]);

                // Mahasiswa 2
                $user2 = User::create([
                    'name' => 'Randy Silfio',
                    'email' => 'randy@sak.com',
                    'password' => Hash::make('password'),
                ]);
                $user2->roles()->attach($mahasiswaRole);
                Mahasiswa::create([
                    'user_id' => $user2->id,
                    'nim' => '672021002',
                    'nama_lengkap' => 'Randy Silfio',
                    'program_studi_id' => $prodi->id,
                ]);
            });
        }
    }
}
