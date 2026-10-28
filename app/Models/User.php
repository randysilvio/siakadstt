<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Laravel 10 membaca properti $casts (method casts() baru dikenal di Laravel 11).
     * Password selalu di-hash eksplisit dengan Hash::make() di controller/impor.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // --- RELASI KE ROLE (Sistem Multi-User) ---
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Cek peran pengguna. Daftar peran dimuat sekali per instance (bukan satu query per pemanggilan),
     * karena layout memanggil hasRole() puluhan kali per halaman.
     * Dosen dengan otoritas modul keuangan (is_keuangan) diperlakukan sebagai peran 'keuangan'.
     */
    public function hasRole($roleName): bool
    {
        $dicari = (array) $roleName;

        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        if ($this->roles->whereIn('name', $dicari)->isNotEmpty()) {
            return true;
        }

        if (in_array('keuangan', $dicari, true) && $this->dosen && $this->dosen->is_keuangan) {
            return true;
        }

        return false;
    }

    /** Muat ulang cache peran setelah peran diubah dalam request yang sama. */
    public function refreshRoles(): static
    {
        $this->unsetRelation('roles');
        return $this;
    }

    public function assignRole($roleName)
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $this->roles()->syncWithoutDetaching($role);
        $this->refreshRoles();
    }

    // --- RELASI PROFIL PENGGUNA ---

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function dosen()
    {
        return $this->hasOne(Dosen::class);
    }

    /**
     * [BARU] Relasi ke Tendik (Tenaga Kependidikan)
     * Ditambahkan untuk fitur pegawai administrasi.
     */
    public function tendik()
    {
        return $this->hasOne(Tendik::class);
    }

    public function camaba()
    {
        return $this->hasOne(Camaba::class);
    }

    // --- FITUR LAINNYA ---

    // Relasi ke Pembayaran (Keuangan)
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    // Cek apakah user adalah Ketua Program Studi
    public function isKaprodi(): bool
    {
        if ($this->hasRole('dosen') && $this->dosen) {
            return ProgramStudi::where('kaprodi_dosen_id', $this->dosen->id)->exists();
        }
        return false;
    }

    /**
     * ID peran yang berlaku bagi akun ini, termasuk peran turunan yang tidak tersimpan di role_user:
     * Kaprodi (dosen yang ditunjuk pada master prodi) dan Keuangan (dosen ber-otoritas keuangan).
     */
    public function idPeranEfektif(): \Illuminate\Support\Collection
    {
        $nama = [];
        if ($this->isKaprodi()) {
            $nama[] = 'kaprodi';
        }
        if ($this->dosen && $this->dosen->is_keuangan) {
            $nama[] = 'keuangan';
        }
        $turunan = $nama ? Role::whereIn('name', $nama)->pluck('id') : collect();

        return $this->roles->pluck('id')->merge($turunan)->unique()->values();
    }

    /**
     * Akun yang memegang salah satu peran (by ID), termasuk peran turunan Kaprodi & Keuangan.
     */
    public static function pemegangPeran(array $roleIds): \Illuminate\Database\Eloquent\Collection
    {
        $namaPeran = Role::whereIn('id', $roleIds)->pluck('name')->all();

        return static::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $roleIds))
            ->when(in_array('kaprodi', $namaPeran, true), fn ($q) => $q->orWhereHas('dosen', fn ($d) => $d->whereIn('id', ProgramStudi::whereNotNull('kaprodi_dosen_id')->select('kaprodi_dosen_id'))))
            ->when(in_array('keuangan', $namaPeran, true), fn ($q) => $q->orWhereHas('dosen', fn ($d) => $d->where('is_keuangan', true)))
            ->get();
    }

    /**
     * Alasan akun tidak boleh dihapus karena terikat riwayat yang harus tetap utuh, atau null bila aman.
     * Menghapus akun akan ikut menghapus (cascade) absensi & sirkulasi perpustakaan, dan menghapus jejak
     * petugas pada verifikasi pembayaran / validasi KRS / keputusan PMB.
     */
    public function alasanAkunTidakBisaDihapus(): ?string
    {
        $cek = [
            'masih meminjam buku perpustakaan'          => fn () => \App\Models\Peminjaman::where('user_id', $this->id)->where('status', 'Dipinjam')->exists(),
            'memiliki riwayat absensi kerja'            => fn () => \App\Models\AbsensiPegawai::where('user_id', $this->id)->exists(),
            'tercatat sebagai verifikator pembayaran'   => fn () => \App\Models\Pembayaran::where('diverifikasi_oleh', $this->id)->exists(),
            'tercatat sebagai validator KRS'            => fn () => \App\Models\KrsPersetujuan::where('divalidasi_oleh', $this->id)->exists(),
            'tercatat sebagai pemutus seleksi PMB'      => fn () => \App\Models\Camaba::where('diputuskan_oleh', $this->id)->exists(),
        ];

        $alasan = collect($cek)->filter(fn ($fn) => $fn())->keys();

        return $alasan->isEmpty()
            ? null
            : 'Akun ini tidak dapat dihapus karena ' . $alasan->implode(', ') . '. Riwayat tersebut wajib tetap tercatat; ganti kata sandi akun bila pegawai sudah tidak bertugas.';
    }

    // --- KEPANITIAAN PMB ---

    public function kepanitiaanPmb()
    {
        return $this->hasMany(PmbKepanitiaan::class);
    }

    /**
     * Tahun PMB yang sedang ditangani pengguna sebagai panitia (kepanitiaan belum ditutup).
     *
     * @return array<int>
     */
    public function tahunPanitiaPmbAktif(): array
    {
        return $this->kepanitiaanPmb()->aktif()->pluck('tahun_pmb')->map(fn ($t) => (int) $t)->all();
    }

    public function isPanitiaPmb(): bool
    {
        return $this->kepanitiaanPmb()->aktif()->exists();
    }

    // Relasi untuk fitur Absensi Pegawai
    public function absensiPegawai()
    {
        return $this->hasMany(AbsensiPegawai::class);
    }
}