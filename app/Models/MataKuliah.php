<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliahs';

    protected $fillable = [
        'kurikulum_id',
        'kode_mk',
        'nama_mk',
        'sks',
        'semester',
        'dosen_id',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function prasyarats()
    {
        return $this->belongsToMany(MataKuliah::class, 'matakuliah_prasyarat', 'mata_kuliah_id', 'prasyarat_id');
    }
    
    public function mahasiswas()
    {
        // =================================================================
        // ===== PERBAIKAN DITAMBAHKAN DI SINI =====
        // =================================================================
        // Menambahkan ->withPivot() untuk memberitahu Laravel agar selalu memuat
        // kolom 'nilai' dan 'tahun_akademik_id' dari tabel perantara.
        // Ini krusial untuk fitur input nilai dan pencetakan PDF.
        return $this->belongsToMany(Mahasiswa::class, 'mahasiswa_mata_kuliah')
                    ->withPivot('nilai', 'tahun_akademik_id')
                    ->withTimestamps();
    }
}
