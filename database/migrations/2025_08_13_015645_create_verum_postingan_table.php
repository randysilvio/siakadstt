<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('verum_postingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('verum_kelas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('konten');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('verum_postingan');
    }
};