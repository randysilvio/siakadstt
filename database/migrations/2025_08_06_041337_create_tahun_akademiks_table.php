<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_akademiks', function (Blueprint $table) {
            $table->id();
            $table->string('tahun');
            // 'Ganjil' disertakan agar driver non-MySQL (yang tidak menjalankan ALTER ENUM) tetap menerima nilai baru
            $table->enum('semester', ['Gasal', 'Ganjil', 'Genap']);
            $table->boolean('is_active')->default(false);
            $table->date('tanggal_mulai_krs');
            $table->date('tanggal_selesai_krs');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tahun_akademiks');
    }
};