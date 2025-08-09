<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProgramStudi; // Import model

class ProgramStudiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProgramStudi::create(['nama_prodi' => 'Teknik Informatika']);
        ProgramStudi::create(['nama_prodi' => 'Sistem Informasi']);
        ProgramStudi::create(['nama_prodi' => 'Manajemen Bisnis']);
        ProgramStudi::create(['nama_prodi' => 'Desain Komunikasi Visual']);
    }
}