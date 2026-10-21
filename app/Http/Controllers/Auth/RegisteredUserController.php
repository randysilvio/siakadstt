<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    /**
     * Mengarahkan halaman registrasi umum ke portal resmi PMB.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('pmb.register');
    }

    /**
     * Memblokir pembuatan akun langsung (Privilege Escalation Prevention).
     */
    public function store(Request $request): RedirectResponse
    {
        abort(403, 'Pendaftaran akun secara langsung ditutup. Silakan melalui jalur resmi PMB.');
    }
}