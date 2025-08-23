<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MahasiswaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->hasRole('mahasiswa')) {
            return $next($request);
        }
        return redirect('/dashboard')->with('error', 'Halaman ini hanya untuk mahasiswa.');
    }
}
