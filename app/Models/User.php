<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan', // <-- PERUBAHAN DI SINI
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Setiap User memiliki satu data Mahasiswa.
     */
    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    /**
     * Setiap User memiliki satu data Dosen.
     */
    public function dosen()
    {
        return $this->hasOne(Dosen::class);
    }

    /**
     * Fungsi baru untuk memeriksa apakah user ini adalah Kaprodi.
     */
    public function isKaprodi(): bool
    {
        // Cek jika user adalah dosen dan data dosennya ada
        if ($this->role == 'dosen' && $this->dosen) {
            // Cek apakah ID dosen ini ada di tabel program_studis sebagai kaprodi
            return ProgramStudi::where('kaprodi_dosen_id', $this->dosen->id)->exists();
        }
        return false;
    }
}
