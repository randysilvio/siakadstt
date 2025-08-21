<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mahasiswa[] $mahasiswas
 * @property-read \App\Models\Dosen|null $kaprodi
 */
class ProgramStudi extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_prodi',
        'kaprodi_dosen_id',
    ];

    /**
     * Relasi ke Mahasiswa (satu prodi punya banyak mahasiswa).
     */
    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    /**
     * Relasi ke Dosen (satu prodi dikepalai oleh satu dosen).
     */
    public function kaprodi()
    {
        return $this->belongsTo(Dosen::class, 'kaprodi_dosen_id');
    }
}