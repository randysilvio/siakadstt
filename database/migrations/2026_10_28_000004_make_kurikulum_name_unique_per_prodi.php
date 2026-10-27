<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nama kurikulum unik per program studi (bukan global). Sebelumnya index global ini dihapus
 * secara diam-diam oleh controller saat runtime; kini dilakukan sekali lewat migrasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('kurikulums', fn (Blueprint $t) => $t->dropUnique('kurikulums_nama_kurikulum_unique'));
        } catch (\Throwable $e) {
            // Index global sudah pernah dilepas
        }

        $duplikat = DB::table('kurikulums')
            ->select('program_studi_id', 'nama_kurikulum')
            ->groupBy('program_studi_id', 'nama_kurikulum')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if (!$duplikat) {
            try {
                Schema::table('kurikulums', fn (Blueprint $t) => $t->unique(['program_studi_id', 'nama_kurikulum'], 'uniq_kurikulum_prodi_nama'));
            } catch (\Throwable $e) {
                // Index sudah ada
            }
        }
    }

    public function down(): void
    {
        try {
            Schema::table('kurikulums', fn (Blueprint $t) => $t->dropUnique('uniq_kurikulum_prodi_nama'));
        } catch (\Throwable $e) {
        }
    }
};
