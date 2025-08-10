<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class KeuanganMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        // Izinkan akses jika user adalah admin ATAU dosen bagian keuangan
        if ($user->role == 'admin' || ($user->role == 'dosen' && $user->dosen?->is_keuangan)) {
            return $next($request);
        }
        return redirect('/dashboard')->with('error', 'Anda tidak memiliki hak akses ke bagian keuangan.');
    }
}