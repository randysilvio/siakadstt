<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Ditambahkan

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

    // --- FUNGSI BARU DITAMBAHKAN ---
    /**
     * Mendefinisikan relasi many-to-many ke model Role.
     * Tabel pivot 'pengumuman_role' diasumsikan ada.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'pengumuman_role');
    }
}