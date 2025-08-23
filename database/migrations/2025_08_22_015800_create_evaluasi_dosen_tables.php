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
        // Tabel untuk mengatur sesi atau periode evaluasi
        Schema::create('evaluasi_sesi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sesi');
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Tabel untuk menyimpan daftar pertanyaan evaluasi
        Schema::create('evaluasi_pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->text('pertanyaan');
            $table->enum('tipe_jawaban', ['skala_1_5', 'teks'])->default('skala_1_5');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        // Tabel untuk menyimpan jawaban yang diberikan oleh mahasiswa
        Schema::create('evaluasi_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_sesi_id')->constrained('evaluasi_sesi')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('dosens')->onDelete('cascade');
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->onDelete('cascade');
            $table->foreignId('evaluasi_pertanyaan_id')->constrained('evaluasi_pertanyaan')->onDelete('cascade');
            $table->unsignedTinyInteger('jawaban_skala')->nullable(); // Untuk tipe skala 1-5
            $table->text('jawaban_teks')->nullable(); // Untuk tipe jawaban teks/esai singkat
            $table->timestamps();
            
            // Mencegah duplikasi data, satu mahasiswa hanya bisa mengisi satu kuesioner
            // untuk dosen dan matkul yang sama dalam satu sesi.
            $table->unique([
                'evaluasi_sesi_id', 
                'mahasiswa_id', 
                'dosen_id', 
                'mata_kuliah_id', 
                'evaluasi_pertanyaan_id'
            ], 'unique_evaluasi_jawaban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_jawaban');
        Schema::dropIfExists('evaluasi_pertanyaan');
        Schema::dropIfExists('evaluasi_sesi');
    }
};