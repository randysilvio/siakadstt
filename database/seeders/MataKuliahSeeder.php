<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataKuliah;

class MataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $matkuls = [
            // Semester 1
            ['kode_mk' => 'IF101', 'nama_mk' => 'Dasar Pemrograman', 'sks' => 4, 'semester' => 1],
            ['kode_mk' => 'MA101', 'nama_mk' => 'Kalkulus I', 'sks' => 3, 'semester' => 1],
            ['kode_mk' => 'FS101', 'nama_mk' => 'Fisika Dasar', 'sks' => 3, 'semester' => 1],
            ['kode_mk' => 'BI101', 'nama_mk' => 'Pendidikan Agama', 'sks' => 2, 'semester' => 1],
            ['kode_mk' => 'KU101', 'nama_mk' => 'Bahasa Indonesia', 'sks' => 2, 'semester' => 1],
            // Semester 2
            ['kode_mk' => 'IF201', 'nama_mk' => 'Struktur Data', 'sks' => 4, 'semester' => 2],
            ['kode_mk' => 'IF202', 'nama_mk' => 'Pemrograman Berorientasi Objek', 'sks' => 4, 'semester' => 2],
            ['kode_mk' => 'MA201', 'nama_mk' => 'Kalkulus II', 'sks' => 3, 'semester' => 2],
            ['kode_mk' => 'IF203', 'nama_mk' => 'Sistem Digital', 'sks' => 3, 'semester' => 2],
            ['kode_mk' => 'KU201', 'nama_mk' => 'Bahasa Inggris', 'sks' => 2, 'semester' => 2],
            // Semester 3
            ['kode_mk' => 'IF301', 'nama_mk' => 'Basis Data', 'sks' => 4, 'semester' => 3],
            ['kode_mk' => 'IF302', 'nama_mk' => 'Jaringan Komputer', 'sks' => 3, 'semester' => 3],
            ['kode_mk' => 'IF303', 'nama_mk' => 'Sistem Operasi', 'sks' => 3, 'semester' => 3],
            ['kode_mk' => 'MA301', 'nama_mk' => 'Aljabar Linear', 'sks' => 3, 'semester' => 3],
            ['kode_mk' => 'ST301', 'nama_mk' => 'Statistika dan Probabilitas', 'sks' => 3, 'semester' => 3],
            // Semester 4
            ['kode_mk' => 'IF401', 'nama_mk' => 'Rekayasa Perangkat Lunak', 'sks' => 3, 'semester' => 4],
            ['kode_mk' => 'IF402', 'nama_mk' => 'Pemrograman Web', 'sks' => 4, 'semester' => 4],
            ['kode_mk' => 'IF403', 'nama_mk' => 'Kecerdasan Buatan', 'sks' => 3, 'semester' => 4],
            ['kode_mk' => 'IF404', 'nama_mk' => 'Analisis dan Desain Algoritma', 'sks' => 3, 'semester' => 4],
            ['kode_mk' => 'KU401', 'nama_mk' => 'Pancasila', 'sks' => 2, 'semester' => 4],
            // Semester 5
            ['kode_mk' => 'IF501', 'nama_mk' => 'Keamanan Informasi', 'sks' => 3, 'semester' => 5],
            ['kode_mk' => 'IF502', 'nama_mk' => 'Pemrograman Mobile', 'sks' => 4, 'semester' => 5],
            ['kode_mk' => 'IF503', 'nama_mk' => 'Grafika Komputer', 'sks' => 3, 'semester' => 5],
            ['kode_mk' => 'IF504', 'nama_mk' => 'Manajemen Proyek TI', 'sks' => 3, 'semester' => 5],
            ['kode_mk' => 'KU501', 'nama_mk' => 'Kewirausahaan', 'sks' => 2, 'semester' => 5],
            // Semester 6
            ['kode_mk' => 'IF601', 'nama_mk' => 'Sistem Terdistribusi', 'sks' => 3, 'semester' => 6],
            ['kode_mk' => 'IF602', 'nama_mk' => 'Data Mining', 'sks' => 3, 'semester' => 6],
            ['kode_mk' => 'IF603', 'nama_mk' => 'Interaksi Manusia dan Komputer', 'sks' => 3, 'semester' => 6],
            ['kode_mk' => 'IF604', 'nama_mk' => 'Etika Profesi', 'sks' => 2, 'semester' => 6],
            ['kode_mk' => 'KU601', 'nama_mk' => 'Kuliah Kerja Nyata', 'sks' => 3, 'semester' => 6],
        ];

        foreach ($matkuls as $mk) {
            MataKuliah::create($mk);
        }
    }
}
