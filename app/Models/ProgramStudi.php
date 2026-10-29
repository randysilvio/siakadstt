<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    use \App\Models\Concerns\TercatatAktivitas;

    public function labelAktivitas(): string
    {
        return 'program studi ' . $this->nama_prodi;
    }

    use HasFactory;

    protected $fillable = [
        'fakultas_id',
        'kode_prodi',
        'nama_prodi',
        'jenjang',
        'kaprodi_dosen_id',
    ];

    public function kurikulums()
    {
        return $this->hasMany(Kurikulum::class);
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_id');
    }

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function kaprodi()
    {
        return $this->belongsTo(Dosen::class, 'kaprodi_dosen_id');
    }

    public function dosens()
    {
        return $this->hasMany(Dosen::class, 'program_studi_id');
    }
}