<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        // 1. Cek Rate Limit (Maksimal 5 kali gagal per 60 detik)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'message' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
                'errors' => [
                    'email' => ["Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik."]
                ]
            ], 429);
        }

        /** @var \App\Models\User|null $user */
        $user = User::with('roles')->where('email', $request->email)->first();

        // 2. Cek User & Password
        if (!$user || !Hash::check($request->password, (string) $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        \App\Models\LogAktivitas::catat('login', 'Masuk lewat aplikasi seluler', $user, null, $user->id);

        // 3. Buat Token Sanctum
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // 4. Tentukan Role Utama
        $roleName = $user->roles->first() ? $user->roles->first()->name : null;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user->only('id', 'name', 'email'),
            'token' => $token,
            'role' => $roleName,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        // Perangkat ini berhenti menerima push untuk akun yang keluar
        if ($user && $request->filled('push_token')) {
            \App\Models\PerangkatPush::where('user_id', $user->id)->where('token', $request->input('push_token'))->delete();
        }
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logout berhasil']);
    }
}