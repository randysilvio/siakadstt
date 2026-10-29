<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - Kalender akademik sebagai panel kendali: jenis acara (informasi / krs / validasi_krs / edom / input_nilai / pmb)
 *   beserta tautan ke Tahun Akademik, Gelombang PMB, dan Sesi EDOM yang dikendalikannya.
 * - Log aktivitas penting (siapa mengubah apa) dan waktu login terakhir pengguna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan_akademik', function (Blueprint $table) {
            $table->string('jenis', 30)->default('informasi')->after('deskripsi');
            $table->foreignId('tahun_akademik_id')->nullable()->after('jenis')->constrained('tahun_akademiks')->nullOnDelete();
            $table->foreignId('pmb_period_id')->nullable()->after('tahun_akademik_id')->constrained('pmb_periods')->nullOnDelete();
            $table->foreignId('evaluasi_sesi_id')->nullable()->after('pmb_period_id')->constrained('evaluasi_sesi')->nullOnDelete();
            $table->json('pengingat_terkirim')->nullable()->after('evaluasi_sesi_id');
            $table->index(['jenis', 'tahun_akademik_id']);
        });

        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 20);                 // login, buat, ubah, hapus, peran
            $table->string('subjek_tipe', 60)->nullable();
            $table->unsignedBigInteger('subjek_id')->nullable();
            $table->string('keterangan', 255);
            $table->json('perubahan')->nullable();      // {kolom: [lama, baru]}
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['subjek_tipe', 'subjek_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('last_login_at'));
        Schema::dropIfExists('log_aktivitas');
        Schema::table('kegiatan_akademik', function (Blueprint $table) {
            $table->dropIndex(['jenis', 'tahun_akademik_id']);
            $table->dropConstrainedForeignId('evaluasi_sesi_id');
            $table->dropConstrainedForeignId('pmb_period_id');
            $table->dropConstrainedForeignId('tahun_akademik_id');
            $table->dropColumn(['jenis', 'pengingat_terkirim']);
        });
    }
};
