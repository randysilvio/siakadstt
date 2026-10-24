<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tracer_studies')) {
            Schema::create('tracer_studies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->unique()->constrained('mahasiswas')->onDelete('cascade');
                $table->year('tahun_lulus');
                $table->string('status_pekerjaan', 100);
                $table->unsignedInteger('waktu_tunggu_bulan')->default(0);
                $table->string('nama_instansi_gereja', 255)->nullable();
                $table->string('jabatan_pekerjaan', 150)->nullable();
                $table->string('kesesuaian_bidang', 50)->default('Sangat Sesuai');
                $table->string('kisaran_pendapatan', 100)->nullable();
                $table->string('kontak_atasan_nama', 150)->nullable();
                $table->string('kontak_atasan_jabatan', 150)->nullable();
                $table->string('kontak_atasan_hp', 30)->nullable();
                $table->text('saran_kurikulum')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tracer_studies');
    }
};