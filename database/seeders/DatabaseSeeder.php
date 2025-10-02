<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /**
         * PERBAIKAN: Memanggil semua seeder yang dibutuhkan dalam urutan yang benar.
         * Urutan ini penting untuk menghindari error karena ketergantungan data
         * (misalnya, Mahasiswa butuh Program Studi, User butuh Role).
         */
        $this->call([
            // 1. Jalankan seeder untuk data master terlebih dahulu
            RoleSeeder::class,
            ProgramStudiSeeder::class,

            // 2. Jalankan seeder untuk membuat user dan data terkait
            AdminUserSeeder::class,
            DosenUserSeeder::class,
            MahasiswaSeeder::class,
            InstitutionalUserSeeder::class, // Untuk user rektorat, keuangan, dll.

            // 3. Jalankan seeder untuk data akademik lainnya
            MataKuliahSeeder::class,
        ]);
    }
}