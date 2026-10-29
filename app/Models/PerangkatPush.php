<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerangkatPush extends Model
{
    protected $table = 'perangkat_push';
    protected $fillable = ['user_id', 'token', 'platform', 'versi_aplikasi', 'terakhir_aktif'];
    protected $casts = ['terakhir_aktif' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Simpan/pindahkan token ke pengguna ini (token yang sama tidak boleh milik dua akun). */
    public static function daftarkan(User $user, string $token, string $platform = 'android', ?string $versi = null): self
    {
        return static::updateOrCreate(['token' => $token], [
            'user_id' => $user->id, 'platform' => $platform, 'versi_aplikasi' => $versi, 'terakhir_aktif' => now(),
        ]);
    }
}
