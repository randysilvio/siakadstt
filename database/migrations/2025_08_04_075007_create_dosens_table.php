<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nidn')->unique();
            $table->string('nama_lengkap');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};