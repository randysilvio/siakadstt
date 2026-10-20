<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perpustakaan_eksemplars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('koleksi_id')->constrained('perpustakaan_koleksi')->onDelete('cascade');
            $table->string('kode_barcode')->unique(); // Contoh: BUKU-001-00001
            $table->enum('status_fisik', ['Tersedia', 'Dipinjam', 'Rusak', 'Hilang'])->default('Tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perpustakaan_eksemplars');
    }
};