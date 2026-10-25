<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function addIndexSafely(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        foreach ($columns as $col) {
            if (!Schema::hasColumn($table, $col)) {
                return;
            }
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Throwable $e) {
            // Abaikan jika index sudah ada
        }
    }

    public function up(): void
    {
        $this->addIndexSafely('mahasiswas', ['status_mahasiswa', 'program_studi_id'], 'idx_mhs_status_prodi');
        $this->addIndexSafely('mahasiswas', ['dosen_wali_id', 'status_krs'], 'idx_mhs_wali_krs');
        $this->addIndexSafely('mahasiswas', ['tahun_masuk'], 'idx_mhs_angkatan');

        $this->addIndexSafely('mahasiswa_mata_kuliah', ['mahasiswa_id', 'tahun_akademik_id'], 'idx_krs_mhs_ta');
        $this->addIndexSafely('mahasiswa_mata_kuliah', ['mata_kuliah_id', 'tahun_akademik_id'], 'idx_krs_mk_ta');

        $this->addIndexSafely('pembayarans', ['mahasiswa_id', 'status'], 'idx_bayar_mhs_status');
        $this->addIndexSafely('pembayarans', ['user_id', 'jenis_pembayaran'], 'idx_bayar_user_jenis');

        $this->addIndexSafely('jadwals', ['mata_kuliah_id', 'hari'], 'idx_jadwal_mk_hari');
        $this->addIndexSafely('verum_kelas', ['tahun_akademik_id', 'dosen_id'], 'idx_verum_ta_dosen');
        $this->addIndexSafely('bimbingan_tugas_akhirs', ['mahasiswa_id', 'status'], 'idx_bimbingan_mhs_status');
        $this->addIndexSafely('pengajuan_surats', ['mahasiswa_id', 'status'], 'idx_surat_mhs_status');
    }

    public function down(): void
    {
        // Index dipertahankan untuk performa
    }
};