<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kurikulums', function (Blueprint $table) {
            // Menambahkan kolom program_studi_id yang terhubung ke tabel program_studis
            // Dibuat nullable() agar data kurikulum yang sudah ada tidak error saat migrasi
            $table->foreignId('program_studi_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('program_studis')
                  ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kurikulums', function (Blueprint $table) {
            // Menghapus foreign key dan kolom jika rollback
            $table->dropForeign(['program_studi_id']);
            $table->dropColumn('program_studi_id');
        });
    }
};