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
        Schema::table('mahasiswas', function (Blueprint $table) {
            // 1. Identitas Personal (Wajib Feeder)
            // Letakkan setelah NIM agar rapi
            $table->string('nik', 16)->nullable()->unique()->after('nim')->comment('Nomor Induk Kependudukan (Wajib Validasi PIN Ijazah)');
            $table->string('nisn', 12)->nullable()->after('nik')->comment('Nomor Induk Siswa Nasional');
            $table->string('kewarganegaraan')->default('WNI')->after('nisn');
            $table->string('jalur_pendaftaran')->default('Mandiri')->after('kewarganegaraan'); // Mandiri, Prestasi, dll

            // 2. Detail Alamat (Wajib Feeder)
            // Letakkan setelah kolom alamat yang sudah ada
            $table->string('dusun')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('jenis_tinggal')->nullable(); // Kos, Asrama, Bersama Ortu
            $table->string('alat_transportasi')->nullable(); // Motor, Jalan Kaki, Angkutan Umum

            // 3. Data Ayah
            $table->string('nik_ayah', 16)->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('pendidikan_ayah')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('penghasilan_ayah')->nullable();

            // 4. Data Ibu (Catatan: 'nama_ibu_kandung' sudah ada di tabel sebelumnya, jadi tidak perlu dibuat lagi)
            $table->string('nik_ibu', 16)->nullable();
            $table->string('pendidikan_ibu')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('penghasilan_ibu')->nullable();

            // 5. Data Wali (Opsional)
            $table->string('nama_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn([
                'nik', 'nisn', 'kewarganegaraan', 'jalur_pendaftaran',
                'dusun', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kode_pos', 'jenis_tinggal', 'alat_transportasi',
                'nik_ayah', 'nama_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
                'nik_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
                'nama_wali', 'pekerjaan_wali'
            ]);
        });
    }
};