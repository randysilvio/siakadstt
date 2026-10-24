<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengajuan_surats')) {
            Schema::create('pengajuan_surats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
                $table->string('jenis_surat', 120);
                $table->string('keperluan', 500);
                $table->string('tujuan_instansi', 255)->nullable();
                $table->string('nomor_surat', 120)->nullable();
                $table->enum('status', ['Menunggu Verifikasi', 'Disetujui', 'Ditolak'])->default('Menunggu Verifikasi');
                $table->text('catatan_admin')->nullable();
                $table->string('kode_verifikasi', 64)->unique();
                $table->timestamp('tanggal_disetujui')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_surats');
    }
};