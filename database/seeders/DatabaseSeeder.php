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
            RoleSeeder::class,
            ProgramStudiSeeder::class,
            AdminUserSeeder::class,
            DosenUserSeeder::class,
            MahasiswaSeeder::class,
            InstitutionalUserSeeder::class,
            MataKuliahSeeder::class,
            ChatbotKnowledgeSeeder::class,
        ]);
    }
}