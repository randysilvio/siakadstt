<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekStatusPembayaranMiddleware
{
    /**
     * Status tagihan yang sudah membuka akses KRS.
     * 'menunggu_konfirmasi' sengaja TIDAK termasuk: bukti transfer yang baru diunggah belum diverifikasi Keuangan.
     */
    public const STATUS_TERBUKA = ['lunas', 'dispensasi'];

    /**
     * Handle an incoming request.
     * Menolak akses KRS selama mahasiswa masih memiliki tagihan yang belum lunas, termasuk yang buktinya
     * masih menunggu verifikasi. Akses dibuka bila seluruh tagihan 'lunas' atau 'dispensasi' (Cicilan Resmi).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $tagihanTertahan = $user->mahasiswa->pembayarans()
                ->whereNotIn('status', self::STATUS_TERBUKA)
                ->pluck('status');

            if ($tagihanTertahan->isNotEmpty()) {
                $hanyaMenungguVerifikasi = $tagihanTertahan->every(fn ($s) => $s === 'menunggu_konfirmasi');

                $pesan = $hanyaMenungguVerifikasi
                    ? 'Akses ke halaman KRS belum dibuka. Bukti pembayaran Anda sedang diverifikasi oleh Bagian Keuangan. KRS dapat diisi setelah tagihan dinyatakan LUNAS atau mendapat Dispensasi Cicilan.'
                    : 'Akses ke halaman KRS ditolak. Anda masih memiliki tagihan keuangan yang belum lunas atau belum mendapatkan persetujuan Dispensasi Cicilan dari Bagian Keuangan.';

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
