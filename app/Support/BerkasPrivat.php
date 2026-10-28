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

    /** File yang diizinkan untuk diunggah ke storage sensitif. */
    public const TYPE_YANG_DIIZINKAN = [
        'jpg', 'jpeg', 'png', 'webp', 'gif',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt', 'rtf',
        'zip', 'rar', '7z',
    ];

    public static function simpan(UploadedFile $file, string $folder, ?array $allowedExtensions = null): string
    {
        self::validasiFileAman($file, $allowedExtensions ?? self::TYPE_YANG_DIIZINKAN);

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

    private static function validasiFileAman(UploadedFile $file, array $allowedExtensions): void
    {
        if (!$file->isValid()) {
            throw new \RuntimeException('Berkas upload tidak valid atau rusak.');
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        $mime = strtolower((string) $file->getMimeType());
        $blockedExt = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'phps', 'exe', 'bat', 'cmd', 'js', 'html', 'htm', 'svg', 'xml', 'jsp', 'asp', 'aspx', 'cfm', 'cgi', 'pl', 'py', 'rb', 'sh'];

        if ($ext === '' || !in_array($ext, $allowedExtensions, true)) {
            throw new \RuntimeException('Tipe berkas tidak diizinkan untuk diunggah.');
        }

        if (in_array($ext, $blockedExt, true) || str_contains($mime, 'php') || str_contains($mime, 'javascript') || str_contains($mime, 'html')) {
            throw new \RuntimeException('Berkas berbahaya tidak diizinkan.');
        }

        $fullPath = $file->getRealPath();
        if ($fullPath && is_readable($fullPath)) {
            $content = @file_get_contents($fullPath);
            if (is_string($content)) {
                $content = strtolower($content);
                if (str_contains($content, '<?php') || str_contains($content, '<script') || str_contains($content, 'javascript:') || str_contains($content, '<svg') || str_contains($content, '<html')) {
                    throw new \RuntimeException('Isi berkas berbahaya terdeteksi.');
                }
            }
        }
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
