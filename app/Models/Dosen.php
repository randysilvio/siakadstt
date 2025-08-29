<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MataKuliah[] $mataKuliahs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mahasiswa[] $mahasiswaWali
 */
class Dosen extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nidn',
        'nama_lengkap',
        'is_keuangan',
        // --- PENAMBAHAN KOLOM BARU UNTUK PROFIL ---
        'jabatan_akademik',
        'bidang_keahlian',
        'deskripsi_diri',
        'email_institusi',
        'link_google_scholar',
        'link_sinta',
        'foto_profil',
    ];

    /**
     * Relasi ke MataKuliah (satu dosen mengajar banyak mata kuliah).
     */
    public function mataKuliahs()
    {
        return $this->hasMany(MataKuliah::class);
    }

    /**
     * Relasi ke User (satu dosen dimiliki oleh satu user).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Mahasiswa (satu dosen bisa menjadi wali bagi banyak mahasiswa).
     */
    public function mahasiswaWali()
    {
        return $this->hasMany(Mahasiswa::class, 'dosen_wali_id');
    }

    /**
     * Accessor untuk mendapatkan URL lengkap foto profil.
     * Ini akan memudahkan pemanggilan gambar di view.
     */
    public function getFotoProfilUrlAttribute()
    {
        if ($this->foto_profil) {
            // Pastikan Anda sudah menjalankan `php artisan storage:link`
            return asset('storage/' . $this->foto_profil);
        }

        // Mengembalikan gambar default jika tidak ada foto
        // Anda bisa menempatkan gambar default di public/images/default-avatar.png
        return asset('images/default-avatar.png');
    }
}