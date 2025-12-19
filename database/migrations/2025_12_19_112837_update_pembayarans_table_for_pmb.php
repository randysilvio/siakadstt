<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // 1. Ubah mahasiswa_id jadi nullable (boleh kosong)
            $table->unsignedBigInteger('mahasiswa_id')->nullable()->change();
            
            // 2. Tambahkan user_id (untuk relasi ke user login, baik mhs/camaba)
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // Kembalikan seperti semula (hati-hati jika ada data null)
            $table->unsignedBigInteger('mahasiswa_id')->nullable(false)->change();
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};