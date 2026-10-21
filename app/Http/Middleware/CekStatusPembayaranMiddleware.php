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

        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $mahasiswa = $user->mahasiswa;

            // Cek jika mahasiswa punya tagihan yang belum lunas
            $memilikiTagihan = $mahasiswa->pembayarans()
                ->whereIn('status', ['belum_lunas', 'belum_bayar'])
                ->exists();

            if ($memilikiTagihan) {
                $pesan = 'Anda memiliki tagihan yang belum lunas. Silakan selesaikan pembayaran untuk mengisi KRS.';
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['status' => 'error', 'message' => $pesan], 403);
                }
                return redirect()->route('dashboard')->with('error', $pesan);
            }
        }

        return $next($request);
    }
}