<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tendik extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_lengkap', 'nip_yayasan', 'nitk', 'nik',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
        'alamat', 'nomor_telepon', 'email_institusi',
        'unit_kerja', 'jabatan', 'jenis_tendik', 'pendidikan_terakhir',
        'status_kepegawaian', 'tmt_kerja', 'foto_profil'
    ];

    // Relasi ke User Login
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Helper untuk Foto
    protected $appends = ['foto_url'];
    public function getFotoUrlAttribute()
    {
        return $this->foto_profil ? asset('storage/' . $this->foto_profil) : asset('images/default-avatar.png');
    }
}