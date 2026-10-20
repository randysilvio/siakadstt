<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjamans', function (Blueprint $table) {
            // Nullable karena data lama mungkin belum menggunakan eksemplar
            $table->foreignId('eksemplar_id')->nullable()->after('koleksi_id')->constrained('perpustakaan_eksemplars')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->dropForeign(['eksemplar_id']);
            $table->dropColumn('eksemplar_id');
        });
    }
};