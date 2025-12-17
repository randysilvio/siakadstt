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

    // Menambahkan atribut virtual ke output JSON
    protected $appends = ['foto_url'];

    /**
     * [PERBAIKAN] Menggunakan Virtual Attribute 'foto_url'.
     * Panggil di View: {{ $dosen->foto_url }}
     * * Atribut asli '$dosen->foto_profil' tetap berisi path murni (misal: "dosen/foto.jpg")
     * agar fungsi Storage::delete($dosen->foto_profil) bekerja dengan benar.
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto_profil) {
            return asset('storage/' . $this->foto_profil);
        }

        // Gambar default lokal
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