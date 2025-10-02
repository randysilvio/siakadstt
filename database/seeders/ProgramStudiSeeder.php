<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramStudi;

class ProgramStudiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProgramStudi::create(['nama_prodi' => 'Teknik Informatika']);
        ProgramStudi::create(['nama_prodi' => 'Sistem Informasi']);
    }
}
