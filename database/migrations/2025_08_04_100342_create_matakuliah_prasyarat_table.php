<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('matakuliah_prasyarat', function (Blueprint $table) {
            $table->id();
            // Kolom untuk mata kuliah yang memiliki prasyarat
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->onDelete('cascade');
            // Kolom untuk mata kuliah yang menjadi prasyarat
            $table->foreignId('prasyarat_id')->constrained('mata_kuliahs')->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('matakuliah_prasyarat');
    }
};