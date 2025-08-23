<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Models\Policy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('inputNilai', function (User $user, MataKuliah $mataKuliah) {
            // 1. Admin selalu diizinkan.
            if ($user->hasRole('admin')) {
                return true;
            }

            // 2. Dosen pengampu mata kuliah diizinkan.
            if ($user->hasRole('dosen') && $user->dosen?->id === $mataKuliah->dosen_id) {
                return true;
            }

            // 3. Kaprodi diizinkan jika mata kuliah ada di prodinya.
            if ($user->hasRole('kaprodi') && $user->dosen) {
                // Cari prodi yang dikepalai oleh dosen ini
                $prodiYangDikepalai = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->first();
                
                // Pastikan prodi dan kurikulum ada sebelum membandingkan
                if ($prodiYangDikepalai && $mataKuliah->kurikulum) {
                    // Cek apakah kurikulum mata kuliah ini milik prodi tersebut
                    return $mataKuliah->kurikulum->program_studi_id === $prodiYangDikepalai->id;
                }
            }

            // Jika tidak memenuhi semua kondisi di atas, tolak.
            return false;
        });
    }
}
