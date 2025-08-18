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
        Schema::create('dokumen_publiks', function (Blueprint $table) {
            $table->id();
            $table->string('judul_dokumen'); // Kolom yang error sebelumnya
            $table->text('deskripsi')->nullable(); // Kolom yang error sekarang
            $table->string('file_path');      // Kemungkinan akan error berikutnya
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_publiks');
    }
};