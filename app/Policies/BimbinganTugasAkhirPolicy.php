<?php

namespace App\Policies;

use App\Models\BimbinganTugasAkhir;
use App\Models\ProgramStudi;
use App\Models\User;

/**
 * Hak akses bimbingan Skripsi / Tugas Akhir & PPL.
 */
class BimbinganTugasAkhirPolicy
{
    /** Admin, mahasiswa pemilik, dosen pembimbing 1/2, atau Kaprodi prodi mahasiswa. */
    public function lihat(User $user, BimbinganTugasAkhir $bimbingan): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->mahasiswa && (int) $bimbingan->mahasiswa_id === (int) $user->mahasiswa->id) {
            return true;
        }
        if ($user->dosen) {
            $dosenId = (int) $user->dosen->id;
            if (in_array($dosenId, [(int) $bimbingan->pembimbing_1_id, (int) $bimbingan->pembimbing_2_id], true)) {
                return true;
            }
            return $this->kaprodiMahasiswa($user, $bimbingan);
        }
        return false;
    }

    /** Menetapkan pembimbing, sidang, kelulusan & nilai: Kaprodi prodi mahasiswa atau Admin. */
    public function putuskan(User $user, BimbinganTugasAkhir $bimbingan): bool
    {
        return $user->hasRole('admin') || ($user->dosen && $this->kaprodiMahasiswa($user, $bimbingan));
    }

    private function kaprodiMahasiswa(User $user, BimbinganTugasAkhir $bimbingan): bool
    {
        return ProgramStudi::where('id', optional($bimbingan->mahasiswa)->program_studi_id)
            ->where('kaprodi_dosen_id', $user->dosen->id)
            ->exists();
    }
}
