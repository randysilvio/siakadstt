<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('verum_kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presensi_id')->constrained('verum_presensi')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->timestamp('waktu_absen');
            $table->timestamps();
            $table->unique(['presensi_id', 'mahasiswa_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('verum_kehadiran');
    }
};