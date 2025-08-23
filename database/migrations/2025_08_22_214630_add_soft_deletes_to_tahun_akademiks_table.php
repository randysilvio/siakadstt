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
        Schema::table('tahun_akademiks', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at'); // Menambahkan kolom deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahun_akademiks', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Menghapus kolom deleted_at
        });
    }
};