<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained()->onDelete('cascade');
            $table->string('hari'); // Contoh: Senin, Selasa, Rabu
            $table->time('jam_mulai'); // Contoh: 08:00
            $table->time('jam_selesai'); // Contoh: 10:00
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwals');
    }
};