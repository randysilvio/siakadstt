<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerumPresensi extends Model
{
    use HasFactory;

    protected $table = 'verum_presensi';

    protected $fillable = [
        'kelas_id',
        'judul_pertemuan',
        'pertemuan_ke',
        'waktu_buka',
        'waktu_tutup'
    ];

    // PENTING: Casting agar field ini dianggap sebagai Carbon Date Object
    protected $casts = [
        'waktu_buka' => 'datetime',
        'waktu_tutup' => 'datetime',
    ];

    public function kelas()
    {
        return $this->belongsTo(VerumKelas::class, 'kelas_id');
    }

    public function kehadiran()
    {
        return $this->hasMany(VerumKehadiran::class, 'presensi_id');
    }
}