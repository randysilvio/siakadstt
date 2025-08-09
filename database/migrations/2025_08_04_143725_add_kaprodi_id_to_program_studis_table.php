<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('program_studis', function (Blueprint $table) {
            $table->foreignId('kaprodi_dosen_id')->nullable()->after('nama_prodi')->constrained('dosens')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::table('program_studis', function (Blueprint $table) {
            $table->dropForeign(['kaprodi_dosen_id']);
            $table->dropColumn('kaprodi_dosen_id');
        });
    }
};