<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Laravel 10 membutuhkan doctrine/dbal untuk ->change() kecuali operasi skema native diaktifkan.
        // Tanpa ini, `php artisan migrate` pada server baru (MySQL) gagal di migrasi yang mengubah kolom.
        Schema::useNativeSchemaOperationsIfPossible();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paginasi seragam SIAKAD (Bootstrap 5, Indonesia, filter dipertahankan): resources/views/vendor/pagination
        Paginator::useBootstrapFive();
        Paginator::defaultView('pagination::siakad');
        Paginator::defaultSimpleView('pagination::siakad-sederhana');

        // Tanggal pada dokumen & notifikasi selalu berbahasa Indonesia (translatedFormat / isoFormat)
        Carbon::setLocale(config('app.locale', 'id'));

        // Batasi pemakaian chatbot agar kuota API tidak dihabiskan satu akun
        RateLimiter::for('chatbot', function (Request $request) {
            return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
        });

        // Berbagi hitungan user online secara real-time ke semua file Blade
        View::composer('*', function ($view) {
            $onlineUsers = Cache::get('siakad-online-users-list', []);
            $view->with('onlineUsersCount', count($onlineUsers));
        });
    }
}
