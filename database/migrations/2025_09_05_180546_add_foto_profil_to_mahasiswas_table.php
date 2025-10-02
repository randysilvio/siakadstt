<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->string('foto_profil')->nullable()->after('nama_ibu_kandung');
        });
    }
    public function down(): void {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn('foto_profil');
        });
    }
};