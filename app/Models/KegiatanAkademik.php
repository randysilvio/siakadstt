<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanAkademik extends Model
{
    use HasFactory;

    /**
     * Menentukan nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'kegiatan_akademik';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'judul_kegiatan',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'target_role',
    ];
}