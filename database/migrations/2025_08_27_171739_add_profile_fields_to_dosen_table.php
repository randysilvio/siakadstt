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
        // PERBAIKAN: Menggunakan nama tabel 'dosens' yang benar
        Schema::table('dosens', function (Blueprint $table) {
            // Kolom 'nidn' sudah ada di struktur database Anda, jadi ini aman.
            $table->string('jabatan_akademik')->nullable()->after('nidn');
            $table->string('bidang_keahlian')->nullable()->after('jabatan_akademik');
            $table->text('deskripsi_diri')->nullable()->after('bidang_keahlian');
            $table->string('email_institusi')->nullable()->unique()->after('deskripsi_diri');
            $table->string('link_google_scholar')->nullable()->after('email_institusi');
            $table->string('link_sinta')->nullable()->after('link_google_scholar');
            $table->string('foto_profil')->nullable()->after('link_sinta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PERBAIKAN: Menggunakan nama tabel 'dosens' yang benar
        Schema::table('dosens', function (Blueprint $table) {
            // Pengecekan kolom dilakukan untuk memastikan rollback tidak error jika dijalankan berulang
            if (Schema::hasColumn('dosens', 'jabatan_akademik')) {
                $table->dropColumn([
                    'jabatan_akademik',
                    'bidang_keahlian',
                    'deskripsi_diri',
                    'email_institusi',
                    'link_google_scholar',
                    'link_sinta',
                    'foto_profil',
                ]);
            }
        });
    }
};