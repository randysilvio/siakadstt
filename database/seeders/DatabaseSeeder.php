<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            DosenUserSeeder::class,
            ProgramStudiSeeder::class,
            MataKuliahSeeder::class,
            MahasiswaSeeder::class, // <-- TAMBAHKAN INI
        ]);
    }
}