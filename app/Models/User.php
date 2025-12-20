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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- RELASI KE ROLE (Sistem Multi-User) ---
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($roleName)
    {
        if (is_array($roleName)) {
            return $this->roles()->whereIn('name', $roleName)->exists();
        }
        return $this->roles()->where('name', $roleName)->exists();
    }
    
    public function assignRole($roleName)
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $this->roles()->syncWithoutDetaching($role);
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

    // Relasi untuk fitur Absensi Pegawai
    public function absensiPegawai()
    {
        return $this->hasMany(AbsensiPegawai::class);
    }
}