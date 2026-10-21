<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('program_studis', 'kode_prodi')) {
            Schema::table('program_studis', function (Blueprint $table) {
                $table->string('kode_prodi', 10)->nullable()->unique()->after('id');
            });

            // Auto-backfill untuk data Program Studi yang sudah ada agar tidak null
            $prodis = DB::table('program_studis')->orderBy('id')->get();
            foreach ($prodis as $index => $prodi) {
                $kodeDefault = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                DB::table('program_studis')
                    ->where('id', $prodi->id)
                    ->update(['kode_prodi' => $kodeDefault]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('program_studis', 'kode_prodi')) {
            Schema::table('program_studis', function (Blueprint $table) {
                $table->dropUnique(['kode_prodi']);
                $table->dropColumn('kode_prodi');
            });
        }
    }
};