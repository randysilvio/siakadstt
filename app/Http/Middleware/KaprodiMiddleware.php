<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User; // <-- Tambahkan ini

class KaprodiMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        // --- PERBAIKAN UTAMA DI SINI ---
        // Pengecekan diubah menjadi:
        // 1. Apakah pengguna sudah login?
        // 2. Apakah pengguna memiliki peran 'kaprodi' secara eksplisit? ATAU
        // 3. Apakah pengguna adalah seorang dosen yang ditunjuk sebagai kaprodi (implisit)?
        if ($user && ($user->hasRole('kaprodi') || $user->isKaprodi())) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'Anda tidak memiliki hak akses sebagai Ketua Program Studi.');
    }
}