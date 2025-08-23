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
            RoleSeeder::class, // <-- Panggil RoleSeeder pertama
            AdminUserSeeder::class,
            DosenUserSeeder::class,
            ProgramStudiSeeder::class,
            MataKuliahSeeder::class,
            MahasiswaSeeder::class,
            InstitutionalUserSeeder::class,
        ]);
    }
}
