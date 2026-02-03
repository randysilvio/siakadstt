<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read object|null $pivot
 * @property-read \App\Models\Dosen|null $dosen
 * @property-read \App\Models\Kurikulum|null $kurikulum
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Jadwal[] $jadwals
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MataKuliah[] $prasyarats
 */
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
        'file_rps',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class);
    }

    // [TAMBAHAN] Helper untuk mengambil Prodi via Kurikulum
    // Ini penting agar filter "Per Prodi" di laporan RPS berjalan lancar
    public function programStudi()
    {
        return $this->hasOneThrough(
            ProgramStudi::class,
            Kurikulum::class,
            'id', // Foreign key on kurikulums table...
            'id', // Foreign key on program_studis table...
            'kurikulum_id', // Local key on mata_kuliahs table...
            'program_studi_id' // Local key on kurikulums table...
        );
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
        return $this->belongsToMany(Mahasiswa::class, 'mahasiswa_mata_kuliah')
                    ->withPivot('nilai', 'tahun_akademik_id')
                    ->withTimestamps();
    }
}