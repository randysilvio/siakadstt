<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\VerumKelas;
use App\Models\VerumMateri;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\MataKuliah::class          => \App\Policies\MataKuliahPolicy::class,
        \App\Models\VerumKelas::class          => \App\Policies\VerumKelasPolicy::class,
        \App\Models\BimbinganTugasAkhir::class => \App\Policies\BimbinganTugasAkhirPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Gate untuk mengelola Kalender Akademik.
         */
        Gate::define('manage-kalender', function (User $user) {
            return $user->hasRole('admin');
        });
    }
}