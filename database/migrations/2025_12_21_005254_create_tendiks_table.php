<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tendiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Identitas Utama
            $table->string('nama_lengkap');
            $table->string('nip_yayasan')->nullable()->unique(); // NIP Internal
            $table->string('nitk')->nullable()->unique()->comment('Nomor Induk Tenaga Kependidikan (Dikti)');
            $table->string('nik', 16)->nullable(); // KTP
            
            // Biodata
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->string('email_institusi')->nullable();

            // Kepegawaian
            $table->string('unit_kerja')->nullable(); // BAAK, Keuangan, dll
            $table->string('jabatan')->nullable(); // Kepala Biro, Staff, dll
            $table->string('jenis_tendik')->nullable(); // Administrasi, Pustakawan, Laboran, Teknisi
            $table->string('pendidikan_terakhir')->nullable(); // SMA, D3, S1
            $table->string('status_kepegawaian')->default('Tetap'); // Tetap, Kontrak
            $table->date('tmt_kerja')->nullable(); // Tanggal Mulai Tugas
            
            $table->string('foto_profil')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tendiks');
    }
};