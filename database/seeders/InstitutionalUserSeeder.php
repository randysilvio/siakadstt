<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class InstitutionalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'penjaminan_mutu' => 'Tim Penjaminan Mutu',
            'rektorat' => 'Pimpinan Rektorat',
            'keuangan' => 'Staf Keuangan',
            'pustakawan' => 'Staf Pustakawan',
        ];

        foreach ($roles as $roleName => $userName) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $user = User::create([
                    'name' => $userName,
                    'email' => "{$roleName}@sak.com",
                    'password' => Hash::make('password'),
                ]);
                $user->roles()->attach($role);
            }
        }
    }
}
