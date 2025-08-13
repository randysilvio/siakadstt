<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOrDosenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Periksa apakah pengguna sudah login
        if (!Auth::check()) {
            return redirect('login');
        }

        $userRole = Auth::user()->role;

        // Izinkan akses hanya jika peran pengguna adalah 'admin' atau 'dosen'
        if ($userRole === 'admin' || $userRole === 'dosen') {
            return $next($request);
        }

        // Jika bukan, tolak akses dengan error 403 Forbidden
        abort(403, 'AKSI TIDAK DIIZINKAN.');
    }
}
