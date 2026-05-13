<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Menggunakan Bootstrap 5 untuk Paginator standar sistem
        Paginator::useBootstrapFive();

        // Berbagi hitungan user online secara real-time ke semua file Blade
        View::composer('*', function ($view) {
            $onlineUsers = Cache::get('siakad-online-users-list', []);
            $view->with('onlineUsersCount', count($onlineUsers));
        });
    }
}