<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('verum_presensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('verum_kelas')->onDelete('cascade');
            $table->string('judul_pertemuan');
            $table->integer('pertemuan_ke');
            $table->timestamp('waktu_buka');
            $table->timestamp('waktu_tutup')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('verum_presensi');
    }
};