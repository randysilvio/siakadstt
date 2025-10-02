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
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Menambahkan kolom untuk catatan kaprodi setelah kolom status_krs
            $table->text('catatan_kaprodi')->nullable()->after('status_krs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Logika untuk menghapus kolom jika migrasi di-rollback
            $table->dropColumn('catatan_kaprodi');
        });
    }
};