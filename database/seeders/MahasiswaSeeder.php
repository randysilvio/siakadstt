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
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prodi = ProgramStudi::first(); // Ambil prodi pertama (Teknik Informatika)
        $mahasiswaRole = Role::where('name', 'mahasiswa')->first();

        if ($prodi && $mahasiswaRole) {
            DB::transaction(function () use ($prodi, $mahasiswaRole) {
                for ($i = 1; $i <= 35; $i++) {
                    $nim = '672021' . sprintf('%03d', $i);
                    $user = User::create([
                        'name' => "Mahasiswa {$i}",
                        'email' => "mahasiswa{$i}@sak.com",
                        'password' => Hash::make('password'),
                    ]);

                    $user->roles()->attach($mahasiswaRole);

                    Mahasiswa::create([
                        'user_id' => $user->id,
                        'nim' => $nim,
                        'nama_lengkap' => "Mahasiswa {$i}",
                        'program_studi_id' => $prodi->id,
                    ]);
                }
            });
        }
    }
}
