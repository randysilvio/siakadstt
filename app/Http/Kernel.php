<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // PERSIAPAN UNTUK SANCTUM: Middleware ini penting untuk otentikasi API
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // Middleware Kustom Anda yang sudah ada
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'keuangan_tendik' => \App\Http\Middleware\KeuanganTendikMiddleware::class,
        'dosen' => \App\Http\Middleware\DosenMiddleware::class,
        'kaprodi' => \App\Http\Middleware\KaprodiMiddleware::class,
        'mahasiswa' => \App\Http\Middleware\MahasiswaMiddleware::class,
        'cek_pembayaran' => \App\Http\Middleware\CekStatusPembayaranMiddleware::class,
        'cek_periode_krs' => \App\Http\Middleware\CekPeriodeKrsMiddleware::class,
        'pustakawan' => \App\Http\Middleware\PustakawanMiddleware::class,
        'admin_or_dosen' => \App\Http\Middleware\AdminOrDosenMiddleware::class,
        'penjaminan_mutu' => \App\Http\Middleware\PenjaminanMutuMiddleware::class,
        'rektorat' => \App\Http\Middleware\RektoratMiddleware::class,
        
        'role' => \App\Http\Middleware\CheckRoleMiddleware::class,
    ];
}