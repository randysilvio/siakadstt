<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Log aktivitas penting: siapa mengubah apa, kapan. Dicatat otomatis oleh trait Concerns\TercatatAktivitas
 * dan pada saat login. Modul Keuangan & Perpustakaan sengaja TIDAK dicatat di sini (terkunci dari Super Admin).
 */
class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    public $timestamps = false;
    protected $fillable = ['user_id', 'aksi', 'subjek_tipe', 'subjek_id', 'keterangan', 'perubahan', 'ip', 'created_at'];
    protected $casts = ['perubahan' => 'array', 'created_at' => 'datetime'];

    /** Bila true, pencatatan otomatis ditunda (mis. saat impor massal) — impor mencatat ringkasan sendiri. */
    public static bool $jeda = false;

    public const LABEL_AKSI = ['login' => 'Masuk', 'buat' => 'Menambah', 'ubah' => 'Mengubah', 'hapus' => 'Menghapus', 'peran' => 'Mengubah peran', 'impor' => 'Mengimpor'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function catat(string $aksi, string $keterangan, ?Model $subjek = null, ?array $perubahan = null, ?int $userId = null): ?self
    {
        try {
            return static::create([
                'user_id'     => $userId ?? Auth::guard('web')->id() ?? optional(request()->user())->id,
                'aksi'        => $aksi,
                'subjek_tipe' => $subjek ? class_basename($subjek) : null,
                'subjek_id'   => $subjek?->getKey(),
                'keterangan'  => mb_substr($keterangan, 0, 255),
                'perubahan'   => $perubahan ?: null,
                'ip'          => app()->runningInConsole() ? null : request()->ip(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            report($e); // pencatatan log tidak boleh menggagalkan proses utama
            return null;
        }
    }

    /** Jalankan proses tanpa pencatatan otomatis per baris. */
    public static function tanpaCatat(callable $fn)
    {
        $sebelum = static::$jeda;
        static::$jeda = true;
        try {
            return $fn();
        } finally {
            static::$jeda = $sebelum;
        }
    }
}
