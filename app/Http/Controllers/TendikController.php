<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TendikController extends Controller
{
    /**
     * Menampilkan formulir untuk membuat akun tendik baru.
     */
    public function create()
    {
        // Mendefinisikan jabatan yang tersedia untuk tendik
        $jabatans = ['pustakawan', 'keuangan', 'tata usaha'];
        return view('tendik.create', compact('jabatans'));
    }

    /**
     * Menyimpan akun tendik baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'jabatan' => ['required', 'string', Rule::in(['pustakawan', 'keuangan', 'tata usaha'])],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'tendik', // Tetapkan role secara otomatis
            'jabatan' => $request->jabatan,
        ]);

        return redirect()->route('dashboard')->with('success', 'Akun tendik berhasil dibuat.');
    }
}