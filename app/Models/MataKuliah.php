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
        'file_rps',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function programStudi()
    {
        return $this->hasOneThrough(
            ProgramStudi::class,
            Kurikulum::class,
            'id',
            'id',
            'kurikulum_id',
            'program_studi_id'
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
                    ->withPivot(
                        'nama_kelas',
                        'nilai',
                        'tahun_akademik_id',
                        'nilai_kehadiran',
                        'nilai_tugas',
                        'nilai_uts',
                        'nilai_uas',
                        'nilai_akhir_angka'
                    )
                    ->withTimestamps();
    }

    public function teamDosens()
    {
        return $this->belongsToMany(Dosen::class, 'dosen_mata_kuliah', 'mata_kuliah_id', 'dosen_id')
                    ->withPivot('is_utama')
                    ->withTimestamps();
    }
}