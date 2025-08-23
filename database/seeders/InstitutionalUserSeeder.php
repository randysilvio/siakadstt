<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class InstitutionalUserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun untuk Tim Penjaminan Mutu
        $userMutu = User::create([
            'name' => 'Tim Penjaminan Mutu',
            'email' => 'mutu@sttgpipapua.ac.id',
            'password' => Hash::make('password'),
        ]);
        $mutuRole = Role::where('name', 'penjaminan_mutu')->first();
        $userMutu->roles()->attach($mutuRole);

        // Akun untuk Pimpinan / Rektorat
        $userRektorat = User::create([
            'name' => 'Rektorat STT GPI Papua',
            'email' => 'rektorat@sttgpipapua.ac.id',
            'password' => Hash::make('password'),
        ]);
        $rektoratRole = Role::where('name', 'rektorat')->first();
        $userRektorat->roles()->attach($rektoratRole);
    }
}
