<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MataKuliah; // <-- Import model

class MataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat beberapa data mata kuliah sampel
        $matkuls = [
            ['kode_mk' => 'IF101', 'nama_mk' => 'Dasar Pemrograman', 'sks' => 3, 'semester' => 1],
            ['kode_mk' => 'MA101', 'nama_mk' => 'Kalkulus I', 'sks' => 3, 'semester' => 1],
            ['kode_mk' => 'IF201', 'nama_mk' => 'Struktur Data', 'sks' => 3, 'semester' => 2],
            ['kode_mk' => 'IF202', 'nama_mk' => 'Pemrograman Berorientasi Objek', 'sks' => 3, 'semester' => 2],
            ['kode_mk' => 'IF301', 'nama_mk' => 'Basis Data', 'sks' => 3, 'semester' => 3],
        ];

        foreach ($matkuls as $mk) {
            MataKuliah::create($mk);
        }
    }
}