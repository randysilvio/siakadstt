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
     * Menolak akses KRS jika mahasiswa masih memiliki tagihan berstatus 'belum_lunas' atau 'belum_bayar'.
     * Jika status tagihan sudah 'lunas' atau 'dispensasi' (Cicilan Resmi), akses KRS dibuka.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $memilikiTunggakan = $user->mahasiswa->pembayarans()
                ->whereIn('status', ['belum_lunas', 'belum_bayar'])
                ->exists();

            if ($memilikiTunggakan) {
                $pesan = 'Akses ke halaman KRS ditolak. Anda masih memiliki tagihan keuangan yang belum lunas atau belum mendapatkan persetujuan Dispensasi Cicilan dari Bagian Keuangan.';

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $pesan,
                    ], 403);
                }

                return redirect()->route('pembayaran.riwayat')->with('error', $pesan);
            }
        }

        return $next($request);
    }
}