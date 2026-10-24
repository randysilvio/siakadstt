<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tracer_studies')) {
            Schema::table('tracer_studies', function (Blueprint $table) {
                if (!Schema::hasColumn('tracer_studies', 'nama_instansi_gereja')) {
                    $table->string('nama_instansi_gereja', 255)->nullable()->after('waktu_tunggu_bulan');
                }
                if (!Schema::hasColumn('tracer_studies', 'jabatan_pekerjaan')) {
                    $table->string('jabatan_pekerjaan', 150)->nullable()->after('nama_instansi_gereja');
                }
                if (!Schema::hasColumn('tracer_studies', 'kisaran_pendapatan')) {
                    $table->string('kisaran_pendapatan', 100)->nullable()->after('kesesuaian_bidang');
                }
                if (!Schema::hasColumn('tracer_studies', 'kontak_atasan_nama')) {
                    $table->string('kontak_atasan_nama', 150)->nullable()->after('kisaran_pendapatan');
                }
                if (!Schema::hasColumn('tracer_studies', 'kontak_atasan_jabatan')) {
                    $table->string('kontak_atasan_jabatan', 150)->nullable()->after('kontak_atasan_nama');
                }
                if (!Schema::hasColumn('tracer_studies', 'kontak_atasan_hp')) {
                    $table->string('kontak_atasan_hp', 30)->nullable()->after('kontak_atasan_jabatan');
                }
            });

            // Ubah kolom kesesuaian_bidang menjadi VARCHAR(50) agar mendukung 'Tidak Sesuai' tanpa error ENUM
            try {
                DB::statement("ALTER TABLE tracer_studies MODIFY COLUMN kesesuaian_bidang VARCHAR(50) DEFAULT 'Sangat Sesuai'");
            } catch (\Exception $e) {
                // Abaikan jika menggunakan SQLite
            }
        }
    }

    public function down(): void
    {
        // Tidak perlu drop kolom untuk menjaga keamanan data
    }
};