<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DosenUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $user = User::create([
                'name' => 'Budi Do Re Mi',
                'email' => 'dosen@sak.com',
                'password' => Hash::make('password'),
            ]);

            $dosenRole = Role::where('name', 'dosen')->first();
            $user->roles()->attach($dosenRole);

            Dosen::create([
                'user_id' => $user->id,
                'nidn' => '1234567890',
                'nama_lengkap' => 'Budi Do Re Mi, S.Kom., M.Kom.',
            ]);
        });
    }
}
