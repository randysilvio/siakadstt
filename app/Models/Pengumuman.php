<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumumans';

    protected $fillable = [
        'judul',
        'slug', // <--- TAMBAHKAN INI
        'kategori',
        'foto',
        'konten',
        'target_role',
    ];

    // INI KUNCI AGAR URL MENJADI SLUG, BUKAN ID
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'pengumuman_role');
    }
}