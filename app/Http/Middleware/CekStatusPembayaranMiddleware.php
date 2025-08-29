<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekStatusPembayaranMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Pastikan pengguna sudah login, adalah mahasiswa, dan data relasi mahasiswa ada
        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $mahasiswa = $user->mahasiswa;

            // Cek jika mahasiswa punya tagihan dengan status 'belum_lunas'
            $memilikiTagihan = $mahasiswa->pembayarans()->where('status', 'belum_lunas')->exists();

            if ($memilikiTagihan) {
                // Jika punya, kembalikan ke dashboard dengan pesan error
                return redirect()->route('dashboard')->with('error', 'Anda memiliki tagihan yang belum lunas. Silakan selesaikan pembayaran untuk mengisi KRS.');
            }
        }

        // Jika tidak punya tagihan atau pengguna bukan mahasiswa, lanjutkan ke halaman tujuan
        return $next($request);
    }
}

