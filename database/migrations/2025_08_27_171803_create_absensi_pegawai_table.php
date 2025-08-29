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
        Schema::create('absensi_pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lokasi_kerja_id')->constrained('lokasi_kerja')->onDelete('cascade');
            $table->date('tanggal_absensi');

            $table->dateTime('waktu_check_in')->nullable();
            $table->decimal('latitude_check_in', 10, 8)->nullable();
            $table->decimal('longitude_check_in', 11, 8)->nullable();
            $table->string('foto_check_in')->nullable();

            $table->dateTime('waktu_check_out')->nullable();
            $table->decimal('latitude_check_out', 10, 8)->nullable();
            $table->decimal('longitude_check_out', 11, 8)->nullable();
            $table->string('foto_check_out')->nullable();
            
            $table->enum('status_kehadiran', ['Hadir', 'Izin', 'Sakit', 'Alpha'])->default('Alpha');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_pegawai');
    }
};