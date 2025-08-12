<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('verum_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('verum_kelas')->onDelete('cascade');
            $table->string('judul');
            $table->text('instruksi');
            $table->timestamp('tenggat_waktu');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('verum_tugas');
    }
};