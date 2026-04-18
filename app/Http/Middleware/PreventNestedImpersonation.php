<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventNestedImpersonation
{
    /**
     * Mencegah user yang sedang dalam mode menyamar untuk mencoba
     * menyamar menjadi user lain (Inception).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('impersonate_by')) {
            return redirect()->back()->with('error', 'Anda sedang dalam mode menyamar. Hentikan penyamaran saat ini terlebih dahulu sebelum menyamar menjadi user lain.');
        }

        return $next($request);
    }
}