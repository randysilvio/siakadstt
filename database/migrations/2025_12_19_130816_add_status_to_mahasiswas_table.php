<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswas', 'status')) {
                // Menambahkan kolom status dengan nilai default 'Aktif'
                // Pilihan: Aktif, Cuti, Non-Aktif, Lulus, Keluar, DO
                $table->string('status')->default('Aktif')->after('nim'); 
            }
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            if (Schema::hasColumn('mahasiswas', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};