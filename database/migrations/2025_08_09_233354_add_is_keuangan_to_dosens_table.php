<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('dosens', function (Blueprint $table) {
            $table->boolean('is_keuangan')->after('nama_lengkap')->default(false);
        });
    }
    public function down(): void {
        Schema::table('dosens', function (Blueprint $table) {
            $table->dropColumn('is_keuangan');
        });
    }
};