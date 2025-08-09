<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekStatusPembayaranMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $mahasiswa = Auth::user()->mahasiswa;

        // Cek jika mahasiswa punya tagihan dengan status 'belum_lunas'
        $memilikiTagihan = $mahasiswa->pembayarans()->where('status', 'belum_lunas')->exists();

        if ($memilikiTagihan) {
            // Jika punya, kembalikan ke dashboard dengan pesan error
            return redirect()->route('dashboard')->with('error', 'Anda memiliki tagihan yang belum lunas. Silakan selesaikan pembayaran untuk mengisi KRS.');
        }

        // Jika tidak punya tagihan, lanjutkan ke halaman KRS
        return $next($request);
    }
}