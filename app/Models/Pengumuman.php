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
        'kategori',
        'foto', // <-- TAMBAHKAN INI
        'konten',
        'target_role',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'pengumuman_role');
    }
}