<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nidn',
        'nama_lengkap',
        'is_keuangan',
        'jabatan_akademik',
        'bidang_keahlian',
        'deskripsi_diri',
        'email_institusi',
        'link_google_scholar',
        'link_sinta',
        'foto_profil',
    ];
    
    // [PERBAIKAN FINAL] Nama accessor diubah agar bisa dipanggil dengan $dosen->foto_profil
    public function getFotoProfilAttribute($value)
    {
        if ($value) {
            // Pastikan Anda sudah menjalankan `php artisan storage:link`
            return asset('storage/' . $value);
        }

        // Mengembalikan gambar default jika tidak ada foto
        // Pastikan gambar default ada di public/images/default-avatar.png
        return asset('images/default-avatar.png');
    }

    public function mataKuliahs()
    {
        return $this->hasMany(MataKuliah::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mahasiswaWali()
    {
        return $this->hasMany(Mahasiswa::class, 'dosen_wali_id');
    }
}