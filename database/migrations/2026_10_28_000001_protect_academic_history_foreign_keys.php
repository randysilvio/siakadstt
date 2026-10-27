<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Melindungi riwayat akademik & keuangan dari penghapusan berantai.
 *
 * Sebelumnya FK berikut memakai ON DELETE CASCADE sehingga menghapus mata kuliah, akun user, atau data
 * mahasiswa ikut menghapus seluruh KRS, nilai, dan tagihan. Setelah migrasi ini database menolak
 * penghapusan tersebut (RESTRICT); controller menampilkan pesan agar data dinonaktifkan saja.
 *
 * Juga menambah unique index KRS (mahasiswa, mata kuliah, tahun akademik) agar tidak ada baris ganda.
 */
return new class extends Migration
{
    /** [tabel, kolom, tabel referensi] */
    private array $targets = [
        ['mahasiswa_mata_kuliah', 'mahasiswa_id', 'mahasiswas'],
        ['mahasiswa_mata_kuliah', 'mata_kuliah_id', 'mata_kuliahs'],
        ['mahasiswa_mata_kuliah', 'tahun_akademik_id', 'tahun_akademiks'],
        ['mahasiswas', 'user_id', 'users'],
        ['pembayarans', 'mahasiswa_id', 'mahasiswas'],
        ['pembayarans', 'user_id', 'users'],
    ];

    private function foreignKeyNames(string $table, string $column): array
    {
        return collect(DB::select(
            'SELECT CONSTRAINT_NAME AS name FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        ))->pluck('name')->all();
    }

    private function setForeignKeyRule(string $table, string $column, string $references, string $rule): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        foreach ($this->foreignKeyNames($table, $column) as $name) {
            Schema::table($table, fn (Blueprint $t) => $t->dropForeign($name));
        }

        Schema::table($table, function (Blueprint $t) use ($column, $references, $rule) {
            $fk = $t->foreign($column)->references('id')->on($references);
            $rule === 'restrict' ? $fk->restrictOnDelete() : $fk->cascadeOnDelete();
        });
    }

    public function up(): void
    {
        // SQLite tidak bisa mengubah FK pada tabel yang sudah ada; perlindungan tetap dijaga di controller.
        if (DB::getDriverName() === 'mysql') {
            foreach ($this->targets as [$table, $column, $references]) {
                $this->setForeignKeyRule($table, $column, $references, 'restrict');
            }
        }

        $duplikat = DB::table('mahasiswa_mata_kuliah')
            ->select('mahasiswa_id', 'mata_kuliah_id', 'tahun_akademik_id')
            ->groupBy('mahasiswa_id', 'mata_kuliah_id', 'tahun_akademik_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplikat === 0) {
            Schema::table('mahasiswa_mata_kuliah', function (Blueprint $t) {
                $t->unique(['mahasiswa_id', 'mata_kuliah_id', 'tahun_akademik_id'], 'uniq_krs_mhs_mk_ta');
            });
        } else {
            // Data ganda tidak dihapus otomatis; admin perlu membersihkannya lalu menjalankan ulang migrasi ini.
            Log::warning("Migrasi KRS: {$duplikat} kombinasi KRS ganda ditemukan, unique index uniq_krs_mhs_mk_ta belum dipasang.");
        }
    }

    public function down(): void
    {
        try {
            Schema::table('mahasiswa_mata_kuliah', fn (Blueprint $t) => $t->dropUnique('uniq_krs_mhs_mk_ta'));
        } catch (\Throwable $e) {
            // Index belum pernah terpasang (ada data ganda saat up)
        }

        if (DB::getDriverName() === 'mysql') {
            foreach ($this->targets as [$table, $column, $references]) {
                $this->setForeignKeyRule($table, $column, $references, 'cascade');
            }
        }
    }
};
