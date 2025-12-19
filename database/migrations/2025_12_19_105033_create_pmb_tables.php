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
        // 1. Tabel Periode/Gelombang PMB
        Schema::create('pmb_periods', function (Blueprint $table) {
            $table->id();
            $table->string('nama_gelombang'); // Contoh: Gelombang 1 2025
            $table->date('tanggal_buka');
            $table->date('tanggal_tutup');
            $table->decimal('biaya_pendaftaran', 10, 2)->default(0);
            $table->boolean('is_active')->default(false); // Hanya satu yang boleh aktif
            $table->timestamps();
        });

        // 2. Tabel Data Calon Mahasiswa (Camaba)
        Schema::create('camabas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pmb_period_id')->constrained('pmb_periods');
            $table->string('no_pendaftaran')->unique()->nullable(); // Generate otomatis nanti
            
            // Pilihan Prodi (Relasi ke tabel program_studis)
            $table->foreignId('pilihan_prodi_1_id')->nullable()->constrained('program_studis');
            $table->foreignId('pilihan_prodi_2_id')->nullable()->constrained('program_studis');
            
            // Data Pribadi Tambahan
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('agama')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            
            // Data Sekolah
            $table->string('sekolah_asal')->nullable();
            $table->string('nisn')->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->decimal('nilai_rata_rata_rapor', 5, 2)->nullable();
            
            // Status Sistem
            $table->enum('status_pendaftaran', ['draft', 'menunggu_verifikasi', 'lulus', 'tidak_lulus'])->default('draft');
            $table->boolean('is_migrated')->default(false); // Penanda sudah jadi mahasiswa
            $table->timestamps();
        });

        // 3. Tabel Dokumen PMB
        Schema::create('pmb_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camaba_id')->constrained('camabas')->onDelete('cascade');
            $table->string('jenis_dokumen'); // Ijazah, KK, KTP, Pas Foto
            $table->string('path_file');
            $table->enum('status_validasi', ['pending', 'valid', 'invalid'])->default('pending');
            $table->text('catatan_admin')->nullable(); // Jika invalid, kenapa?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pmb_documents');
        Schema::dropIfExists('camabas');
        Schema::dropIfExists('pmb_periods');
    }
};