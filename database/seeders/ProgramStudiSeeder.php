<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramStudi;

class ProgramStudiSeeder extends Seeder
{
    public function run(): void
    {
        ProgramStudi::firstOrCreate(
            ['kode_prodi' => '77201'],
            ['nama_prodi' => 'Teologi (Kependetaan)', 'jenjang' => 'S1']
        );

        ProgramStudi::firstOrCreate(
            ['kode_prodi' => '86202'],
            ['nama_prodi' => 'Pendidikan Agama Kristen (PAK)', 'jenjang' => 'S1']
        );
    }
}