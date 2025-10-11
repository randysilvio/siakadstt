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
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();

            // Menghubungkan ke buku di tabel perpustakaan_koleksi
            $table->foreignId('koleksi_id')->constrained('perpustakaan_koleksi')->onDelete('cascade');

            // Menghubungkan ke peminjam di tabel users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->date('tanggal_pinjam');
            $table->date('jatuh_tempo');
            $table->date('tanggal_kembali')->nullable(); // Dibuat nullable karena awalnya kosong
            
            // Status peminjaman: 'Dipinjam', 'Kembali', 'Terlambat'
            $table->string('status')->default('Dipinjam');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};