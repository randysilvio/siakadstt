<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verum_kelas', function (Blueprint $table) {
            // Menyimpan status apakah meeting aktif
            $table->boolean('is_meeting_active')->default(false)->after('kode_kelas');
        });
    }

    public function down(): void
    {
        Schema::table('verum_kelas', function (Blueprint $table) {
            $table->dropColumn('is_meeting_active');
        });
    }
};