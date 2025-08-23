<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    // RELASI BARU: Many-to-Many ke Role
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // FUNGSI BANTU BARU: Untuk memeriksa apakah user memiliki peran tertentu
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }
    
    // FUNGSI BANTU BARU: Untuk memberikan peran ke user
    public function assignRole($roleName)
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $this->roles()->syncWithoutDetaching($role);
    }

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function dosen()
    {
        return $this->hasOne(Dosen::class);
    }

    public function isKaprodi(): bool
    {
        if ($this->hasRole('dosen') && $this->dosen) {
            return ProgramStudi::where('kaprodi_dosen_id', $this->dosen->id)->exists();
        }
        return false;
    }
}
