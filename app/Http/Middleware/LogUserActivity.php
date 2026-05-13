<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $expiresAt = now()->addMinutes(5); // Durasi status online aktif (5 menit)
            $userId = Auth::user()->id;

            // Mengambil daftar user online saat ini dari Cache
            $onlineUsers = Cache::get('siakad-online-users-list', []);
            $onlineUsers[$userId] = now()->timestamp;

            // Pembersihan mandiri: Hapus ID user yang pasif lebih dari 5 menit
            $limaMenitLalu = now()->subMinutes(5)->timestamp;
            foreach ($onlineUsers as $id => $lastActivity) {
                if ($lastActivity < $limaMenitLalu) {
                    unset($onlineUsers[$id]);
                }
            }

            // Simpan kembali array bersih ke dalam Cache
            Cache::put('siakad-online-users-list', $onlineUsers, $expiresAt);
        }

        return $next($request);
    }
}