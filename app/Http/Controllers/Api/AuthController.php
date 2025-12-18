<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle a login request for the mobile application.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /** @var \App\Models\User|null $user */
        $user = User::where('email', $request->email)->first();

        // 1. Cek User & Password
        if (!$user || !Hash::check($request->password, (string)$user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password yang Anda masukkan salah.'],
            ]);
        }

        // 2. [PERBAIKAN] HAPUS BLOKIRAN MAHASISWA
        // Kode yang memblokir 'hasRole(mahasiswa)' sudah dihapus di sini
        // agar mahasiswa bisa masuk dan mengakses fitur KHS/KRS.

        // 3. Buat Token
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user->only('id', 'name', 'email'), 
            'token' => $token,
            // Opsional: Kirim role agar frontend bisa langsung redirect (Dashboard vs Home)
            'role' => $user->getRoleNames()->first() 
        ]);
    }

    /**
     * Handle a logout request for the mobile application.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user) {
            /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }
        }

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}