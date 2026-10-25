<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Auto-recovery jika user adalah Camaba tetapi rolenya sempat terlepas
        if (in_array('camaba', $roles, true) && !$user->hasRole('camaba') && $user->camaba && !$user->camaba->is_migrated) {
            $roleCamaba = Role::firstOrCreate(
                ['name' => 'camaba'],
                ['display_name' => 'Calon Mahasiswa Baru (PMB)']
            );
            $user->roles()->syncWithoutDetaching([$roleCamaba->id]);
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
            if ($role === 'kaprodi' && method_exists($user, 'isKaprodi') && $user->isKaprodi()) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak. Anda tidak memiliki otorisasi untuk fitur ini.'
            ], 403);
        }

        return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}