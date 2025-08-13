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
        // Pastikan pengguna sudah login dan perannya adalah mahasiswa
        if (Auth::check() && Auth::user()->role == 'mahasiswa') {
            $mahasiswa = Auth::user()->mahasiswa;

            // PERBAIKAN: Tambahkan pengecekan jika $mahasiswa ada (tidak null)
            // Ini untuk mencegah error jika ada akun user yang tidak terhubung ke data mahasiswa.
            if ($mahasiswa) {
                // Cek jika mahasiswa punya tagihan dengan status 'belum_lunas'
                $memilikiTagihan = $mahasiswa->pembayarans()->where('status', 'belum_lunas')->exists();

                if ($memilikiTagihan) {
                    // Jika punya, kembalikan ke dashboard dengan pesan error
                    return redirect()->route('dashboard')->with('error', 'Anda memiliki tagihan yang belum lunas. Silakan selesaikan pembayaran untuk mengisi KRS.');
                }
            }
        }

        // Jika tidak punya tagihan atau bukan mahasiswa, lanjutkan ke halaman tujuan
        return $next($request);
    }
}
