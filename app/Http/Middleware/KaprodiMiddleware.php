<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgramStudi;
use Symfony\Component\HttpFoundation\Response;

class KaprodiMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Cek apakah user adalah dosen dan terdaftar sebagai kaprodi di salah satu prodi
        if ($user->role == 'dosen' && ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->exists()) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'Anda tidak memiliki hak akses sebagai Ketua Program Studi.');
    }
}