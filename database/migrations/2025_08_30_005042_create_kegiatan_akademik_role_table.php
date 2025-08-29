<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('kegiatan_akademik_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_akademik_id')->constrained('kegiatan_akademik')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            // timestamps() tidak diperlukan untuk tabel pivot sederhana ini
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_akademik_role');
    }
};
