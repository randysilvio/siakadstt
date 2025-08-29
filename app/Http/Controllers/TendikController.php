<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role; // Import model Role untuk mengambil data peran
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules; // Menggunakan rules password default dari Laravel

class TendikController extends Controller
{
    /**
     * Menampilkan formulir untuk membuat akun tendik/pengguna baru.
     * Kode ini sepenuhnya baru dan disesuaikan dengan struktur database multi-role.
     */
    public function create()
    {
        // Ambil semua peran yang ada di database, KECUALI peran 'mahasiswa'.
        // Ini agar admin tidak bisa membuat akun mahasiswa dari form ini.
        $roles = Role::where('name', '!=', 'mahasiswa')->get();

        // Kirim data roles ke view 'tendik.create'
        return view('tendik.create', compact('roles'));
    }

    /**
     * Menyimpan akun tendik/pengguna baru ke database.
     * Kode ini sepenuhnya baru dan disesuaikan untuk menyimpan user dan menetapkan role.
     */
    public function store(Request $request)
    {
        // 1. Validasi semua input dari form
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'integer', 'exists:roles,id'], // Memastikan role_id yang dikirim valid dan ada di tabel roles
        ]);

        // 2. Buat record baru di tabel 'users'
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. Lampirkan (attach) peran yang dipilih ke user yang baru dibuat.
        // Ini akan membuat record baru di tabel pivot 'role_user'.
        $user->roles()->attach($request->role_id);

        // 4. Redirect kembali ke halaman daftar pengguna dengan pesan sukses.
        // PERBAIKAN: Menggunakan nama rute 'admin.user.index' yang sudah benar.
        return redirect()->route('admin.user.index')->with('success', 'Akun pengguna baru berhasil dibuat.');
    }
}