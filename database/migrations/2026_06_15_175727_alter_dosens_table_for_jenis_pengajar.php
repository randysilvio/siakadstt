<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            // Mengubah kolom NIDN menjadi boleh kosong (nullable)
            $table->string('nidn')->nullable()->change();
            
            // Menambahkan kategori pengajar (default 'Dosen Tetap' agar data lama otomatis terisi)
            $table->string('jenis_pengajar')->default('Dosen Tetap')->after('nama_lengkap');
        });
    }

    public function down(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            // Mengembalikan ke pengaturan semula jika di-rollback
            $table->string('nidn')->nullable(false)->change();
            $table->dropColumn('jenis_pengajar');
        });
    }
};