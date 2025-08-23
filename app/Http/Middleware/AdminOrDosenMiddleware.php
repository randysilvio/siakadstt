<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOrDosenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->hasRole(['admin', 'dosen'])) {
            return $next($request);
        }
        abort(403, 'AKSI TIDAK DIIZINKAN.');
    }
}
