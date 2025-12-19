<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // Menambahkan kolom jenis_pembayaran setelah kolom user_id
            // Default 'spp' untuk data lama agar tidak error
            $table->string('jenis_pembayaran')->default('spp')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn('jenis_pembayaran');
        });
    }
};