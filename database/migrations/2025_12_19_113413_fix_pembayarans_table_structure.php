<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // 1. Tambah kolom 'keterangan' (Mengatasi Error Anda saat ini)
            if (!Schema::hasColumn('pembayarans', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('status');
            }

            // 2. Tambah kolom 'bukti_bayar' (Mencegah Error saat Camaba upload struk nanti)
            if (!Schema::hasColumn('pembayarans', 'bukti_bayar')) {
                $table->string('bukti_bayar')->nullable()->after('jumlah');
            }

            // 3. Tambah kolom 'tanggal_bayar' (Jika belum ada)
            if (!Schema::hasColumn('pembayarans', 'tanggal_bayar')) {
                $table->date('tanggal_bayar')->nullable()->after('bukti_bayar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['keterangan', 'bukti_bayar', 'tanggal_bayar']);
        });
    }
};