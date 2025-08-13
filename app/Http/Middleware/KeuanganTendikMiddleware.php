<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class KeuanganTendikMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (Auth::check()) {
            // Admin selalu punya akses
            if ($user->role == 'admin') {
                return $next($request);
            }

            // Periksa hak akses untuk tendik keuangan
            if ($user->role == 'tendik' && $user->jabatan == 'keuangan') {
                return $next($request);
            }

            // Periksa hak akses untuk dosen keuangan menggunakan is_keuangan
            if ($user->role == 'dosen' && $user->dosen && $user->dosen->is_keuangan) {
                return $next($request);
            }
        }

        return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}