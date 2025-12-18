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
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /** @var \App\Models\User|null $user */
        $user = User::with('roles')->where('email', $request->email)->first();

        // 1. Cek User & Password
        if (!$user || !Hash::check($request->password, (string)$user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password yang Anda masukkan salah.'],
            ]);
        }

        // 2. Buat Token
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        // 3. Ambil Role (FIX: Gunakan relasi roles() manual, bukan getRoleNames())
        // Mengambil role pertama yang dimiliki user
        $roleName = $user->roles->first() ? $user->roles->first()->name : null;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user->only('id', 'name', 'email'), 
            'token' => $token,
            'role' => $roleName // Kirim role agar frontend tahu (dosen/mahasiswa)
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logout berhasil']);
    }
}