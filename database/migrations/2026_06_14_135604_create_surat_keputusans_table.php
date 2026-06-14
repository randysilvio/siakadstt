<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keputusans', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_surat'); // Contoh: SK Panitia, SK Mengajar, Surat Tugas
            $table->string('nomor_surat')->nullable();
            $table->string('judul');
            $table->date('tanggal_terbit')->nullable();
            
            // Segmen Dinamis Form Builder (Disimpan sebagai Array/JSON)
            $table->json('menimbang')->nullable();
            $table->json('mengingat')->nullable();
            $table->json('memperhatikan')->nullable();
            $table->json('menetapkan')->nullable();
            
            // Untuk jenis surat biasa/keterangan yang tidak memakai format Diktum SK
            $table->longText('isi_surat')->nullable(); 
            
            $table->json('tembusan')->nullable();

            // Bagian Pengesahan (Bisa diubah manual, tapi default sudah tersetting)
            $table->string('penandatangan_jabatan')->default('Kepala Kantor Perwakilan Sinode');
            $table->string('penandatangan_nama')->nullable();
            
            $table->string('file_path')->nullable(); // Tempat simpan scan PDF final
            $table->enum('status', ['Draf', 'Menunggu Tanda Tangan', 'Selesai'])->default('Draf');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keputusans');
    }
};