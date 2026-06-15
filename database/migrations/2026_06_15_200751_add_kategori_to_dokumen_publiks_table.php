<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumen_publiks', function (Blueprint $table) {
            // Ditambahkan nilai default 'Umum' agar ratusan dokumen lama tidak error/kosong
            $table->string('kategori')->default('Umum')->after('judul_dokumen');
        });
    }

    public function down(): void
    {
        Schema::table('dokumen_publiks', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};