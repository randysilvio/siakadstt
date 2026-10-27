<?php

use App\Models\User;

// Registrasi publik umum ditutup; pendaftaran akun baru hanya melalui jalur PMB.

test('halaman register diarahkan ke pendaftaran PMB', function () {
    $this->get('/register')->assertRedirect(route('pmb.register'));
});

test('registrasi umum lewat POST /register tidak membuat akun', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
});
