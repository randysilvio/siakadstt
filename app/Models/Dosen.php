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
        // --- Identitas Pribadi (Feeder) ---
        'nik', 
        'nuptk', 
        'npwp', 
        'tempat_lahir', 
        'tanggal_lahir', 
        'jenis_kelamin', 
        'nomor_telepon', 
        'alamat',
        // --- Kepegawaian ---
        'status_kepegawaian', 
        'no_sk_pengangkatan', 
        'tmt_sk_pengangkatan', 
        'pangkat_golongan',
        // --- Akademik ---
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

    public function getFotoUrlAttribute()
    {
        if ($this->foto_profil) {
            return asset('storage/' . $this->foto_profil);
        }
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