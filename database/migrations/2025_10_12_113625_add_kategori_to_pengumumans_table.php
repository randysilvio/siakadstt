<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            // Tambahkan kolom 'kategori' setelah kolom 'judul'
            // Default 'pengumuman' akan diterapkan pada data lama yang sudah ada
            $table->string('kategori')->default('pengumuman')->after('judul');
        });
    }

    public function down(): void
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};