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
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            // Menambahkan kolom file_rps setelah kolom sks
            $table->string('file_rps')->nullable()->after('sks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->dropColumn('file_rps');
        });
    }
};