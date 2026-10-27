<?php

namespace App\Policies;

use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Hak akses dosen terhadap mata kuliah (pengampu utama atau anggota team teaching).
 */
class MataKuliahPolicy
{
    /** Dosen pengampu utama atau anggota team teaching. */
    public function ampu(User $user, MataKuliah $mataKuliah): bool
    {
        $dosen = $user->dosen;
        if (!$dosen) {
            return false;
        }
        if ((int) $dosen->id === (int) $mataKuliah->dosen_id) {
            return true;
        }
        return DB::table('dosen_mata_kuliah')
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->where('dosen_id', $dosen->id)
            ->exists();
    }

    /** Mengisi buku nilai: Admin atau dosen pengampu. */
    public function kelolaNilai(User $user, MataKuliah $mataKuliah): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('dosen') && $this->ampu($user, $mataKuliah));
    }
}
