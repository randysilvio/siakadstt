<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracer_studies', function (Blueprint $table) {
            $table->id();$table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
            $table->year('tahun_lulus');$table->string('status_pekerjaan', 100);
            $table->integer('waktu_tunggu_bulan')->default(0);$table->string('nama_instansi_jemaat', 255)->nullable();
            $table->string('jabatan_pelayanan', 150)->nullable();$table->enum('kesesuaian_bidang', ['Sangat Sesuai', 'Sesuai', 'Kurang Sesuai'])->default('Sangat Sesuai');
            $table->string('rentang_penghasilan', 100)->nullable();$table->string('kontak_atasan', 150)->nullable();
            $table->text('saran_kurikulum')->nullable();$table->timestamps();

            $table->unique('mahasiswa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracer_studies');
    }
};