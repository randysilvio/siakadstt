<?php

namespace App\Imports\Concerns;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\User;
use RuntimeException;

/**
 * Pencocokan data referensi pada impor Excel secara TEPAT (bukan LIKE %...%), agar baris
 * yang salah ketik tidak diam-diam masuk ke prodi / dosen wali yang keliru.
 */
trait ResolveReferensiImpor
{
    protected int $nomorBaris = 0;

    protected function majukanBaris(): int
    {
        if ($this->nomorBaris === 0) {
            $this->nomorBaris = $this->headingRow();
        }
        return ++$this->nomorBaris;
    }

    protected function gagal(string $pesan): never
    {
        throw new RuntimeException('Baris ' . $this->nomorBaris . ': ' . $pesan);
    }

    /** Prodi dicocokkan dengan ID, kode prodi, atau nama prodi persis (tanpa membedakan huruf besar/kecil). */
    protected function cariProdi($input): ?ProgramStudi
    {
        $nilai = trim((string) $input);
        if ($nilai === '') {
            return null;
        }
        if (ctype_digit($nilai) && ($prodi = ProgramStudi::find((int) $nilai))) {
            return $prodi;
        }
        return ProgramStudi::whereRaw('LOWER(kode_prodi) = ?', [mb_strtolower($nilai)])
            ->orWhereRaw('LOWER(nama_prodi) = ?', [mb_strtolower($nilai)])
            ->first();
    }

    /** Dosen dicocokkan dengan ID, NIDN, atau nama lengkap persis. */
    protected function cariDosen($input): ?Dosen
    {
        $nilai = ltrim(trim((string) $input), "'");
        if ($nilai === '') {
            return null;
        }
        return Dosen::where('nidn', $nilai)
            ->orWhereRaw('LOWER(nama_lengkap) = ?', [mb_strtolower($nilai)])
            ->first()
            ?? (ctype_digit($nilai) ? Dosen::find((int) $nilai) : null);
    }

    /**
     * Password impor: bila diisi minimal 8 karakter; bila kosong dibuat acak (pengguna memakai fitur Lupa Password).
     */
    protected function passwordImpor($input): string
    {
        $password = trim((string) $input);
        if ($password === '') {
            return \Illuminate\Support\Str::password(14);
        }
        if (mb_strlen($password) < 8) {
            $this->gagal('password minimal 8 karakter (kosongkan agar dibuat acak).');
        }
        return $password;
    }

    /** Akun staf/pimpinan tidak boleh ditimpa oleh baris impor yang kebetulan memakai email yang sama. */
    protected function akunBolehDipakai(User $user, array $roleDiizinkan): bool
    {
        return $user->roles()->whereNotIn('name', $roleDiizinkan)->doesntExist();
    }
}
