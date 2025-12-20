<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            // Identitas Personal (Wajib Feeder)
            $table->string('nik', 16)->nullable()->after('nidn')->comment('NIK KTP');
            $table->string('nuptk', 20)->nullable()->after('nik')->comment('Nomor Unik Pendidik dan Tenaga Kependidikan');
            $table->string('npwp', 20)->nullable()->after('nuptk');
            $table->string('tempat_lahir')->nullable()->after('nama_lengkap');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('tanggal_lahir');
            $table->string('nomor_telepon')->nullable()->after('email_institusi');
            $table->text('alamat')->nullable()->after('nomor_telepon');

            // Data Kepegawaian
            $table->string('status_kepegawaian')->default('Dosen Tetap')->after('is_keuangan'); // Dosen Tetap, Dosen Tidak Tetap
            $table->string('no_sk_pengangkatan')->nullable();
            $table->date('tmt_sk_pengangkatan')->nullable(); // Terhitung Mulai Tanggal
            $table->string('pangkat_golongan')->nullable(); // Penata Muda Tk.I (III/b), dll
        });
    }

    public function down(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            $table->dropColumn([
                'nik', 'nuptk', 'npwp', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 
                'nomor_telepon', 'alamat', 'status_kepegawaian', 
                'no_sk_pengangkatan', 'tmt_sk_pengangkatan', 'pangkat_golongan'
            ]);
        });
    }
};