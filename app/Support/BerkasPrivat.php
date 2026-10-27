<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Penyimpanan berkas sensitif (bukti bayar, dokumen PMB, draf skripsi, jawaban tugas, SK, foto absensi)
 * di disk privat `local` (storage/app/private/...), sehingga tidak bisa diunduh lewat URL /storage tanpa login.
 *
 * Berkas lama yang masih berada di disk `public` tetap terbaca (fallback) sampai dipindahkan dengan
 * perintah `php artisan siakad:amankan-berkas`.
 */
class BerkasPrivat
{
    public const DISK = 'local';
    public const PREFIX = 'private';

    public static function simpan(UploadedFile $file, string $folder): string
    {
        return $file->store(self::PREFIX . '/' . trim($folder, '/'), self::DISK);
    }

    /** Disk tempat berkas berada saat ini, atau null bila tidak ditemukan. */
    public static function diskUntuk(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (Storage::disk(self::DISK)->exists($path)) {
            return self::DISK;
        }
        if (Storage::disk('public')->exists($path)) {
            return 'public';
        }
        return null;
    }

    public static function ada(?string $path): bool
    {
        return self::diskUntuk($path) !== null;
    }

    public static function hapus(?string $path): void
    {
        if ($disk = self::diskUntuk($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    public static function pathAbsolut(?string $path): ?string
    {
        $disk = self::diskUntuk($path);
        return $disk ? Storage::disk($disk)->path($path) : null;
    }
}
