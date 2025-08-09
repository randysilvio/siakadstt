<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    // TAMBAHKAN BARIS INI UNTUK MEMAKSA NAMA TABEL
    protected $table = 'pengumumans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'judul',
        'konten',
        'target_role',
    ];
}