<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_mk',
        'nama_mk',
        'sks',
        'semester',
        'dosen_id',
    ];

    public function mahasiswas()
    {
        return $this->belongsToMany(Mahasiswa::class, 'mahasiswa_mata_kuliah');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function prasyarats()
    {
        return $this->belongsToMany(MataKuliah::class, 'matakuliah_prasyarat', 'mata_kuliah_id', 'prasyarat_id');
    }

    /**
     * Relasi ke Jadwal (satu mata kuliah bisa punya banyak jadwal).
     */
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }
}