<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_prodi',
        'kaprodi_dosen_id',
    ];

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function kaprodi()
    {
        return $this->belongsTo(Dosen::class, 'kaprodi_dosen_id');
    }

    // TAMBAHKAN RELASI INI
    public function dosens()
    {
        return $this->hasMany(Dosen::class, 'program_studi_id');
    }
}