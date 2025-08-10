<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('nama_lengkap');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('tanggal_lahir');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->text('alamat')->nullable()->after('agama');
            $table->string('nomor_telepon')->nullable()->after('alamat');
            $table->string('nama_ibu_kandung')->nullable()->after('nomor_telepon');
            $table->enum('status_mahasiswa', ['Aktif', 'Cuti', 'Lulus', 'Drop Out', 'Non-Aktif'])->default('Aktif')->after('dosen_wali_id');
            $table->year('tahun_masuk')->nullable()->after('status_mahasiswa');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'agama',
                'alamat',
                'nomor_telepon',
                'nama_ibu_kandung',
                'status_mahasiswa',
                'tahun_masuk',
            ]);
        });
    }
};