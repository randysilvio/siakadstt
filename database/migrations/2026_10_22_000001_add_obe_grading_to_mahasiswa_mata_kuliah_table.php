<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa_mata_kuliah', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswa_mata_kuliah', 'nilai_kehadiran')) {
                $table->decimal('nilai_kehadiran', 5, 2)->nullable()->after('tahun_akademik_id');
            }
            if (!Schema::hasColumn('mahasiswa_mata_kuliah', 'nilai_tugas')) {
                $table->decimal('nilai_tugas', 5, 2)->nullable()->after('nilai_kehadiran');
            }
            if (!Schema::hasColumn('mahasiswa_mata_kuliah', 'nilai_uts')) {
                $table->decimal('nilai_uts', 5, 2)->nullable()->after('nilai_tugas');
            }
            if (!Schema::hasColumn('mahasiswa_mata_kuliah', 'nilai_uas')) {
                $table->decimal('nilai_uas', 5, 2)->nullable()->after('nilai_uts');
            }
            if (!Schema::hasColumn('mahasiswa_mata_kuliah', 'nilai_akhir_angka')) {
                $table->decimal('nilai_akhir_angka', 5, 2)->nullable()->after('nilai_uas');
            }
        });

        Schema::table('verum_pengumpulan', function (Blueprint $table) {
            if (!Schema::hasColumn('verum_pengumpulan', 'catatan_dosen')) {
                $table->text('catatan_dosen')->nullable()->after('nilai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa_mata_kuliah', function (Blueprint $table) {
            $cols = ['nilai_kehadiran', 'nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_akhir_angka'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('mahasiswa_mata_kuliah', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('verum_pengumpulan', function (Blueprint $table) {
            if (Schema::hasColumn('verum_pengumpulan', 'catatan_dosen')) {
                $table->dropColumn('catatan_dosen');
            }
        });
    }
};