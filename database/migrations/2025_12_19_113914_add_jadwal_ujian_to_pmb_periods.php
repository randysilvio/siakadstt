<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->date('tanggal_ujian')->nullable()->after('biaya_pendaftaran');
            $table->time('jam_mulai_ujian')->nullable()->after('tanggal_ujian');
            $table->time('jam_selesai_ujian')->nullable()->after('jam_mulai_ujian');
            $table->enum('jenis_ujian', ['online', 'offline'])->default('offline')->after('jam_selesai_ujian');
            $table->string('lokasi_ujian')->nullable()->after('jenis_ujian'); // Bisa link Zoom atau Ruangan
        });
    }

    public function down(): void
    {
        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->dropColumn(['tanggal_ujian', 'jam_mulai_ujian', 'jam_selesai_ujian', 'jenis_ujian', 'lokasi_ujian']);
        });
    }
};