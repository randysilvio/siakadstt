<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            // Tambahkan kolom slug setelah judul
            $table->string('slug')->nullable()->after('judul');
        });
    }

    public function down()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};