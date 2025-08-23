<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PenjaminanMutuMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->hasRole('penjaminan_mutu')) {
            return $next($request);
        }
        return redirect('/login')->with('error', 'Anda tidak memiliki hak akses.');
    }
}
