<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VerumKelas;

/**
 * Hak akses kelas daring Verum.
 */
class VerumKelasPolicy
{
    /** Dosen pembuat kelas atau pengampu mata kuliahnya (team teaching). */
    public function kelola(User $user, VerumKelas $kelas): bool
    {
        if (!$user->dosen) {
            return false;
        }
        if ((int) $user->dosen->id === (int) $kelas->dosen_id) {
            return true;
        }
        return $kelas->mataKuliah && (new MataKuliahPolicy())->ampu($user, $kelas->mataKuliah);
    }

    /** Mahasiswa peserta resmi (KRS disetujui pada semester kelas & rombel sesuai). */
    public function ikuti(User $user, VerumKelas $kelas): bool
    {
        return $user->mahasiswa !== null && $kelas->isPeserta($user->mahasiswa);
    }

    public function lihat(User $user, VerumKelas $kelas): bool
    {
        return $this->kelola($user, $kelas) || $this->ikuti($user, $kelas);
    }
}
