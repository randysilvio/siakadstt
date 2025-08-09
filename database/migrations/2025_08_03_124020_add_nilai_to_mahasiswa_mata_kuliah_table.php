<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa_mata_kuliah', function (Blueprint $table) {
            // Kolom untuk menyimpan nilai (misal: A, B, C), bisa null
            $table->string('nilai', 2)->nullable()->after('mata_kuliah_id');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa_mata_kuliah', function (Blueprint $table) {
            $table->dropColumn('nilai');
        });
    }
};