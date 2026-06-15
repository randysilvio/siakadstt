<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_keputusans', function (Blueprint $table) {
            // [TAMBAHAN] Kolom JSON untuk menyimpan Tendik, BEM, atau Pihak Eksternal
            $table->json('panitia_lainnya')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keputusans', function (Blueprint $table) {
            $table->dropColumn('panitia_lainnya');
        });
    }
};