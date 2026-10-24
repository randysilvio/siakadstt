<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_tridharmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('dosens')->onDelete('cascade');
            $table->enum('unsur', ['penelitian', 'pengabdian', 'penunjang']);
            $table->string('kategori', 150);
            $table->string('judul', 300);
            $table->string('peran', 100)->default('Ketua / Penulis Utama');
            $table->year('tahun');
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil');
            $table->decimal('sks_bkd', 4, 2)->default(1.00);
            $table->decimal('angka_kredit', 6, 2)->default(0.00);
            $table->string('nama_jurnal_penerbit_lokasi', 255)->nullable();
            $table->string('nomor_identitas', 150)->nullable(); // ISSN / ISBN / DOI / No. Surat Tugas
            $table->string('tautan_eksternal', 500)->nullable();
            $table->string('file_bukti', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_tridharmas');
    }
};