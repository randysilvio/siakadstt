<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // LANGKAH 1: Ubah tipe kolom menjadi VARCHAR untuk sementara agar bisa diupdate
        DB::statement("ALTER TABLE tahun_akademiks CHANGE COLUMN semester semester VARCHAR(255) NOT NULL");

        // LANGKAH 2: Perbarui data yang sudah ada dari 'Gasal' menjadi 'Ganjil'
        DB::table('tahun_akademiks')
            ->where('semester', 'Gasal')
            ->update(['semester' => 'Ganjil']);

        // LANGKAH 3: Setelah data aman, ubah kembali struktur kolom ke ENUM yang baru
        DB::statement("ALTER TABLE tahun_akademiks CHANGE COLUMN semester semester ENUM('Ganjil', 'Genap') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // LANGKAH 1 (Reverse): Ubah tipe kolom menjadi VARCHAR untuk sementara
        DB::statement("ALTER TABLE tahun_akademiks CHANGE COLUMN semester semester VARCHAR(255) NOT NULL");

        // LANGKAH 2 (Reverse): Ubah kembali data dari 'Ganjil' ke 'Gasal'
        DB::table('tahun_akademiks')
            ->where('semester', 'Ganjil')
            ->update(['semester' => 'Gasal']);
            
        // LANGKAH 3 (Reverse): Kembalikan struktur kolom ENUM ke kondisi semula
        DB::statement("ALTER TABLE tahun_akademiks CHANGE COLUMN semester semester ENUM('Gasal', 'Genap') NOT NULL");
    }
};
