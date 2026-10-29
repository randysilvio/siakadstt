<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Token push (FCM) per perangkat: satu pengguna bisa punya beberapa HP & browser. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perangkat_push', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 512)->unique();
            $table->string('platform', 20)->default('android'); // android | ios | web
            $table->string('versi_aplikasi', 30)->nullable();
            $table->timestamp('terakhir_aktif')->nullable();
            $table->timestamps();
        });

        // Token web lama (kolom users.fcm_token) ikut dipindahkan
        foreach (DB::table('users')->whereNotNull('fcm_token')->where('fcm_token', '!=', '')->get(['id', 'fcm_token']) as $u) {
            DB::table('perangkat_push')->insertOrIgnore([
                'user_id' => $u->id, 'token' => mb_substr($u->fcm_token, 0, 512), 'platform' => 'web',
                'terakhir_aktif' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkat_push');
    }
};
