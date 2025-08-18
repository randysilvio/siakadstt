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
    
    protected $fillable = ['user_id', 'nidn', 'nama_lengkap', 'is_keuangan'];

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
}