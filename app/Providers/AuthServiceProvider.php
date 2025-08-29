<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        // 'App\Models\Model' => 'App\Models\Policy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Gate untuk otorisasi input nilai.
         * Diizinkan untuk: Admin, Dosen Pengampu, dan Kaprodi yang menaungi mata kuliah.
         */
        Gate::define('inputNilai', function (User $user, MataKuliah $mataKuliah) {
            if ($user->hasRole('admin')) {
                return true;
            }
            if ($user->hasRole('dosen') && $user->dosen?->id === $mataKuliah->dosen_id) {
                return true;
            }
            if ($user->hasRole('kaprodi') && $user->dosen) {
                $prodiYangDikepalai = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->first();
                if ($prodiYangDikepalai && $mataKuliah->kurikulum) {
                    return $mataKuliah->kurikulum->program_studi_id === $prodiYangDikepalai->id;
                }
            }
            return false;
        });

        /**
         * Gate untuk otorisasi update kelas Verum (hanya dosen pembuat).
         */
        Gate::define('update', function(User $user, VerumKelas $verumKelas){
            return $user->dosen?->id === $verumKelas->dosen_id;
        });

        /**
         * Gate untuk otorisasi hapus materi Verum (hanya dosen pembuat kelasnya).
         */
        Gate::define('delete', function(User $user, VerumMateri $verumMateri){
            return $user->dosen?->id === $verumMateri->kelas?->dosen_id;
        });

        /**
         * Gate untuk mengelola Kalender Akademik.
         * Saat ini hanya diizinkan untuk Admin.
         */
        Gate::define('manage-kalender', function (User $user) {
            return $user->hasRole('admin');
        });
    }
}
