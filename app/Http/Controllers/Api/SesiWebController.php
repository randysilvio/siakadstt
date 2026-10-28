<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Masuk otomatis ke SIAKAD web dari aplikasi (popup di dalam aplikasi, tanpa login ulang).
 * Aplikasi meminta tautan sekali pakai (berlaku 2 menit) lalu membukanya di WebView.
 */
class SesiWebController extends Controller
{
    public const MASA_BERLAKU_DETIK = 120;

    public static function kunciCache(string $token): string
    {
        return 'sesi-web:' . hash('sha256', $token);
    }

    /** Hanya jalur internal (/...) yang diizinkan sebagai tujuan, mencegah pengalihan ke situs lain. */
    public static function tujuanAman(?string $tujuan): string
    {
        $tujuan = trim((string) $tujuan);
        if ($tujuan === '' || !str_starts_with($tujuan, '/') || str_starts_with($tujuan, '//') || str_contains($tujuan, '\\') || preg_match('/[\x00-\x1F]/', $tujuan)) {
            return '/dashboard';
        }
        return $tujuan;
    }

    public function buat(Request $request): JsonResponse
    {
        $request->validate(['tujuan' => 'nullable|string|max:300']);

        $token = Str::random(64);
        Cache::put(self::kunciCache($token), [
            'user_id' => $request->user()->id,
            'tujuan'  => self::tujuanAman($request->input('tujuan')),
        ], self::MASA_BERLAKU_DETIK);

        return response()->json([
            'url'        => route('masuk.aplikasi', $token),
            'berlaku'    => self::MASA_BERLAKU_DETIK,
        ]);
    }
}
