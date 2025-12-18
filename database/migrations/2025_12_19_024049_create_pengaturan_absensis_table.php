<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengaturan_absensi', function (Blueprint $table) {
            $table->id();
            $table->time('jam_masuk')->default('08:00:00');
            $table->time('jam_pulang')->default('16:00:00');
            $table->integer('toleransi_terlambat_menit')->default(15);
            $table->timestamps();
        });

        // Insert Data Default (WAJIB ADA)
        DB::table('pengaturan_absensi')->insert([
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
            'toleransi_terlambat_menit' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('pengaturan_absensi');
    }
};