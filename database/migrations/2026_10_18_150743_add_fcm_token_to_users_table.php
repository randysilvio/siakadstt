<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi (Menambahkan kolom).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom fcm_token untuk menyimpan alamat perangkat dari Firebase
            // Posisinya diletakkan tepat setelah kolom 'password'
            $table->text('fcm_token')->nullable()->after('password');
        });
    }

    /**
     * Kembalikan migrasi (Menghapus kolom jika di-rollback).
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menghapus kolom fcm_token
            $table->dropColumn('fcm_token');
        });
    }
};