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
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Gate untuk otorisasi input nilai.
         * Diizinkan untuk: Admin, Dosen Pengampu (Utama & Team Teaching), dan Kaprodi yang menaungi mata kuliah.
         */
        Gate::define('inputNilai', function (User $user, MataKuliah $mataKuliah) {
            if ($user->hasRole('admin')) {
                return true;
            }

            if ($user->hasRole('dosen') && $user->dosen) {
                if ((int) $user->dosen->id === (int) $mataKuliah->dosen_id) {
                    return true;
                }

                $isTeamTeaching = DB::table('dosen_mata_kuliah')
                    ->where('mata_kuliah_id', $mataKuliah->id)
                    ->where('dosen_id', $user->dosen->id)
                    ->exists();

                if ($isTeamTeaching) {
                    return true;
                }
            }

            if (($user->hasRole('kaprodi') || $user->isKaprodi()) && $user->dosen) {
                $prodiYangDikepalai = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->first();
                if ($prodiYangDikepalai && $mataKuliah->kurikulum) {
                    return (int) $mataKuliah->kurikulum->program_studi_id === (int) $prodiYangDikepalai->id;
                }
            }

            return false;
        });

        /**
         * Gate untuk otorisasi update kelas Verum (dosen pembuat atau team teaching).
         */
        Gate::define('update', function (User $user, VerumKelas $verumKelas) {
            return $user->dosen && (int) $user->dosen->id === (int) $verumKelas->dosen_id;
        });

        /**
         * Gate untuk otorisasi hapus materi Verum (dosen pembuat kelasnya).
         */
        Gate::define('delete', function (User $user, VerumMateri $verumMateri) {
            return $user->dosen && (int) $user->dosen->id === (int) $verumMateri->kelas?->dosen_id;
        });

        /**
         * Gate untuk mengelola Kalender Akademik.
         */
        Gate::define('manage-kalender', function (User $user) {
            return $user->hasRole('admin');
        });
    }
}