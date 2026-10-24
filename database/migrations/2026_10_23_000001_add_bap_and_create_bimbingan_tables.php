<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom Jurnal & BAP pada tabel verum_presensi
        Schema::table('verum_presensi', function (Blueprint $table) {
            if (!Schema::hasColumn('verum_presensi', 'materi_aktual')) {
                $table->text('materi_aktual')->nullable()->after('judul_pertemuan');
            }
            if (!Schema::hasColumn('verum_presensi', 'metode_pembelajaran')) {
                $table->string('metode_pembelajaran', 100)->default('Tatap Muka / Ceramah & Diskusi')->after('materi_aktual');
            }
        });

        // 2. Tabel Master Bimbingan Tugas Akhir (Skripsi) & PPL / Vikariat
        if (!Schema::hasTable('bimbingan_tugas_akhirs')) {
            Schema::create('bimbingan_tugas_akhirs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
                $table->enum('jenis_bimbingan', ['Skripsi / Tugas Akhir', 'PPL / Vikariat'])->default('Skripsi / Tugas Akhir');
                $table->string('judul', 350);
                $table->string('lokasi_pelayanan', 255)->nullable();
                $table->text('abstrak_ringkas')->nullable();
                $table->foreignId('pembimbing_1_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->foreignId('pembimbing_2_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->enum('status', ['Diajukan', 'Disetujui / Aktif Bimbingan', 'Siap Sidang', 'Selesai / Lulus', 'Revisi Judul'])->default('Diajukan');
                $table->date('tanggal_sidang')->nullable();
                $table->string('ruang_sidang', 100)->nullable();
                $table->decimal('nilai_angka', 5, 2)->nullable();
                $table->string('nilai_huruf', 2)->nullable();
                $table->text('catatan_kaprodi')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tabel Logbook Pertemuan Bimbingan
        if (!Schema::hasTable('bimbingan_logs')) {
            Schema::create('bimbingan_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bimbingan_id')->constrained('bimbingan_tugas_akhirs')->onDelete('cascade');
                $table->foreignId('dosen_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->date('tanggal_bimbingan');
                $table->string('topik_bab', 200);
                $table->text('catatan_mahasiswa')->nullable();
                $table->text('arahan_dosen')->nullable();
                $table->string('file_draf', 255)->nullable();
                $table->enum('status_paraf', ['Menunggu Paraf', 'Disetujui / Lanjut', 'Revisi'])->default('Menunggu Paraf');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bimbingan_logs');
        Schema::dropIfExists('bimbingan_tugas_akhirs');

        Schema::table('verum_presensi', function (Blueprint $table) {
            foreach (['materi_aktual', 'metode_pembelajaran'] as $col) {
                if (Schema::hasColumn('verum_presensi', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};