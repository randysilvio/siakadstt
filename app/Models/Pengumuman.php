<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pengumuman extends Model
{
    use \App\Models\Concerns\TercatatAktivitas;

    /** Kolom yang tidak perlu masuk log aktivitas. */
    protected array $kolomTidakDicatat = ['konten', 'slug'];

    public function labelAktivitas(): string
    {
        return 'pengumuman "' . $this->judul . '"';
    }

    use HasFactory;

    protected static function booted(): void
    {
        // Gambar pratinjau media sosial ikut dibersihkan
        static::deleted(fn (self $p) => \App\Support\GambarBagikan::hapusUntukPengumuman($p->id));
    }

    protected $table = 'pengumumans';

    protected $fillable = [
        'judul',
        'slug', // <--- TAMBAHKAN INI
        'kategori',
        'foto',
        'konten',
        'target_role',
    ];

    // INI KUNCI AGAR URL MENJADI SLUG, BUKAN ID
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'pengumuman_role');
    }

    /**
     * Peran sistem yang termasuk sasaran "tendik" (tenaga kependidikan). Tidak ada role bernama 'tendik';
     * sasaran ini dipetakan ke seluruh role staf non-akademik.
     */
    public const ROLE_TENDIK = ['keuangan', 'pustakawan', 'administrasi_umum', 'penjaminan_mutu', 'rektorat'];

    /**
     * Daftar nama role yang menjadi sasaran pengumuman ini.
     */
    public static function roleSasaran(string $targetRole): array
    {
        return $targetRole === 'tendik' ? self::ROLE_TENDIK : [$targetRole];
    }

    /**
     * Nilai target_role yang relevan bagi kumpulan role seorang pengguna (untuk filter dashboard).
     */
    public static function targetUntukRoles(array $userRoles): array
    {
        $target = array_merge(['semua'], $userRoles);
        if (array_intersect($userRoles, self::ROLE_TENDIK)) {
            $target[] = 'tendik';
        }
        return array_values(array_unique($target));
    }

    public function bisaDibacaOleh(?\App\Models\User $user): bool
    {
        if ($this->target_role === 'semua') {
            return true;
        }
        if (!$user) {
            return false;
        }
        return $user->hasRole('admin') || $user->hasRole(array_merge(self::roleSasaran($this->target_role), ['administrasi_umum']));
    }
}
