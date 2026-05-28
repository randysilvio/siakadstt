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
        Schema::table('jadwals', function (Blueprint $table) {
            // Menambahkan kolom ruang setelah kolom jam_selesai, boleh kosong (nullable)
            $table->string('ruang')->nullable()->after('jam_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            // Menghapus kolom ruang jika migrasi di-rollback
            $table->dropColumn('ruang');
        });
    }
};