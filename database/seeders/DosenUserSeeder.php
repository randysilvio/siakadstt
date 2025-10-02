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
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dosenRole = Role::where('name', 'dosen')->first();

        if ($dosenRole) {
            DB::transaction(function () use ($dosenRole) {
                for ($i = 1; $i <= 10; $i++) {
                    $user = User::create([
                        'name' => "Dosen Pengajar {$i}",
                        'email' => "dosen{$i}@sak.com",
                        'password' => Hash::make('password'),
                    ]);

                    $user->roles()->attach($dosenRole);

                    Dosen::create([
                        'user_id' => $user->id,
                        'nidn' => '00112233' . sprintf('%02d', $i), // Contoh NIDN unik
                        'nama_lengkap' => "Dr. Dosen Pengajar {$i}, S.Kom., M.Kom.",
                    ]);
                }
            });
        }
    }
}
