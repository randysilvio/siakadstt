<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mahasiswa; // Tambahkan ini
use App\Models\ProgramStudi; // Tambahkan ini
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Mengirim data program studi ke view registrasi
        $programStudis = ProgramStudi::orderBy('nama_prodi')->get();
        return view('auth.register', compact('programStudis'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:admin,mahasiswa'], // Validasi untuk role
            
            // Validasi kondisional: wajib jika rolenya mahasiswa
            'nim' => ['required_if:role,mahasiswa', 'nullable', 'string', 'max:255', 'unique:'.Mahasiswa::class],
            'program_studi_id' => ['required_if:role,mahasiswa', 'nullable', 'exists:program_studis,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Simpan role ke tabel user
        ]);

        // Jika rolenya adalah 'mahasiswa', buat juga data di tabel mahasiswas
        if ($request->role === 'mahasiswa') {
            Mahasiswa::create([
                'user_id' => $user->id,
                'nim' => $request->nim,
                'nama_lengkap' => $request->name,
                'program_studi_id' => $request->program_studi_id,
                'status_mahasiswa' => 'Aktif', // Default status saat mendaftar
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}