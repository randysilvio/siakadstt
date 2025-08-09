<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID Program Studi pertama untuk dijadikan sampel
        $prodi = ProgramStudi::first();

        if ($prodi) {
            DB::transaction(function () use ($prodi) {
                // Mahasiswa 1
                $user1 = User::create([
                    'name' => 'Valeria Lesilolo',
                    'email' => 'valeria@sak.com',
                    'password' => Hash::make('password'),
                    'role' => 'mahasiswa',
                ]);

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
                    'role' => 'mahasiswa',
                ]);

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