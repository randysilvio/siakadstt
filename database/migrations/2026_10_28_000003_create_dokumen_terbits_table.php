<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registri dokumen resmi yang dicetak (KRS, KHS, Transkrip, Surat Aktif). Setiap cetakan mendapat kode
 * verifikasi unik yang terikat pada ringkasan ISI dokumen saat dicetak, sehingga halaman verifikasi publik
 * dapat menampilkan isi asli untuk dibandingkan dengan dokumen fisik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_terbits', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 40)->unique();
            $table->string('jenis', 30);
            $table->foreignId('mahasiswa_id')->nullable()->constrained('mahasiswas')->nullOnDelete();
            $table->json('ringkasan');
            $table->string('sidik', 64);
            $table->foreignId('diterbitkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['mahasiswa_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_terbits');
    }
};
