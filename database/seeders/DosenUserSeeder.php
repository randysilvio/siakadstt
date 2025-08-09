<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DosenUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Buat Akun Login
            $user = User::create([
                'name' => 'Budi Do Re Mi',
                'email' => 'dosen@sak.com',
                'password' => Hash::make('password'),
                'role' => 'dosen',
            ]);

            // Buat Data Dosen yang terhubung
            Dosen::create([
                'user_id' => $user->id,
                'nidn' => '1234567890',
                'nama_lengkap' => 'Budi Do Re Mi, S.Kom., M.Kom.',
            ]);
        });
    }
}