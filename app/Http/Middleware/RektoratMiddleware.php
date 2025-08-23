<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RektoratMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->hasRole('rektorat')) {
            return $next($request);
        }
        return redirect('/login')->with('error', 'Anda tidak memiliki hak akses.');
    }
}
