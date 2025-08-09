<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan semua alias middleware Anda di sini
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'dosen' => \App\Http\Middleware\DosenMiddleware::class,
            'mahasiswa' => \App\Http\Middleware\MahasiswaMiddleware::class,
            'cek_pembayaran' => \App\Http\Middleware\CekStatusPembayaranMiddleware::class,
            'cek_periode_krs' => \App\Http\Middleware\CekPeriodeKrsMiddleware::class,
            'kaprodi' => \App\Http\Middleware\KaprodiMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();