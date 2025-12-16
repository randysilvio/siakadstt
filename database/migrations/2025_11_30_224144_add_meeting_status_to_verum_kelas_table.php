<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verum_kelas', function (Blueprint $table) {
            // CEK DULU: Hanya buat kolom jika kolom tersebut BELUM ada
            if (!Schema::hasColumn('verum_kelas', 'is_meeting_active')) {
                $table->boolean('is_meeting_active')->default(false)->after('kode_kelas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('verum_kelas', function (Blueprint $table) {
            // Cek dulu sebelum hapus, untuk menghindari error saat rollback
            if (Schema::hasColumn('verum_kelas', 'is_meeting_active')) {
                $table->dropColumn('is_meeting_active');
            }
        });
    }
};