<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Fakultas (Untuk Skala Institut / Universitas)
        if (!Schema::hasTable('fakultas')) {
            Schema::create('fakultas', function (Blueprint $table) {
                $table->id();
                $table->string('kode_fakultas', 15)->unique();
                $table->string('nama_fakultas', 255);
                $table->foreignId('dekan_dosen_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 2. Relasi Fakultas pada Program Studi
        Schema::table('program_studis', function (Blueprint $table) {
            if (!Schema::hasColumn('program_studis', 'fakultas_id')) {
                $table->foreignId('fakultas_id')->nullable()->after('id')->constrained('fakultas')->nullOnDelete();
            }
            if (!Schema::hasColumn('program_studis', 'jenjang')) {
                $table->string('jenjang', 10)->default('S1')->after('nama_prodi');
            }
        });

        // 3. Kolom Kelas Paralel (Rombel A/B/C) & Kuota Kursi pada Tabel Jadwal
        Schema::table('jadwals', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwals', 'nama_kelas')) {
                $table->string('nama_kelas', 30)->default('A')->after('mata_kuliah_id');
            }
            if (!Schema::hasColumn('jadwals', 'kuota')) {
                $table->unsignedInteger('kuota')->default(40)->after('ruang');
            }
        });

        // 4. Kolom Kelas pada KRS Mahasiswa
        Schema::table('mahasiswa_mata_kuliah', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswa_mata_kuliah', 'nama_kelas')) {
                $table->string('nama_kelas', 30)->default('A')->after('tahun_akademik_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa_mata_kuliah', function (Blueprint $table) {
            if (Schema::hasColumn('mahasiswa_mata_kuliah', 'nama_kelas')) {
                $table->dropColumn('nama_kelas');
            }
        });

        Schema::table('jadwals', function (Blueprint $table) {
            foreach (['nama_kelas', 'kuota'] as $col) {
                if (Schema::hasColumn('jadwals', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('program_studis', function (Blueprint $table) {
            if (Schema::hasColumn('program_studis', 'fakultas_id')) {
                $table->dropForeign(['fakultas_id']);
                $table->dropColumn('fakultas_id');
            }
            if (Schema::hasColumn('program_studis', 'jenjang')) {
                $table->dropColumn('jenjang');
            }
        });

        Schema::dropIfExists('fakultas');
    }
};