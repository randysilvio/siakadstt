<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya menghapus kolom 'jabatan'
        if (Schema::hasColumn('users', 'jabatan')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('jabatan');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan')->nullable()->after('email');
        });
    }
};