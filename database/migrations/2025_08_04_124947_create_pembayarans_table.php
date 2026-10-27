<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            // Nullable sejak awal agar instalasi baru konsisten dengan migrasi PMB (tagihan camaba tanpa mahasiswa_id)
            $table->foreignId('mahasiswa_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('jumlah');
            $table->string('semester');
            $table->date('tanggal_bayar')->nullable();
            $table->string('status')->default('belum_lunas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};