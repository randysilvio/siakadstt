<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_surat_keputusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_keputusan_id')->constrained('surat_keputusans')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->string('jabatan_dalam_surat')->nullable(); // Contoh: Ketua, Sekretaris, Anggota
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_surat_keputusan');
    }
};