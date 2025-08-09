<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mahasiswa; // <-- Import Mahasiswa
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; // <-- Import DB Facade

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Gunakan transaction untuk memastikan kedua data berhasil dibuat
        DB::transaction(function () use ($request) {
            // 1. Buat User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 2. Buat Mahasiswa yang terhubung
            // Untuk sementara, NIM dibuat unik berdasarkan timestamp, dan prodi ID=1
            Mahasiswa::create([
                'user_id' => $user->id,
                'nama_lengkap' => $request->name,
                'nim' => time(), // Placeholder NIM unik
                'program_studi_id' => 1, // Placeholder Prodi, pastikan ada prodi dengan ID=1
            ]);

            event(new Registered($user));
            Auth::login($user);
        });

        return redirect(route('dashboard', absolute: false));
    }
}