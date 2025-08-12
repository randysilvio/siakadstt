<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('verum_pengumpulan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('verum_tugas')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
            $table->string('file_path');
            $table->timestamp('waktu_pengumpulan');
            $table->float('nilai')->nullable();
            $table->timestamps();
            $table->unique(['tugas_id', 'mahasiswa_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('verum_pengumpulan');
    }
};