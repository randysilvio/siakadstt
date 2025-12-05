<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerumKelas extends Model
{
    use HasFactory;

    protected $table = 'verum_kelas';

    protected $fillable = [
        'mata_kuliah_id',
        'dosen_id',
        'tahun_akademik_id',
        'nama_kelas',
        'deskripsi',
        'kode_kelas',
        'is_meeting_active', // <-- Kolom yang baru kita tambah di Langkah 1
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * PERBAIKAN FATAL:
     * Menambahkan parameter kedua 'kelas_id' secara eksplisit.
     * Tanpa ini, Laravel mencari 'verum_kelas_id' dan menyebabkan error 500.
     */
    public function materi()
    {
        return $this->hasMany(VerumMateri::class, 'kelas_id'); 
    }

    public function tugas()
    {
        return $this->hasMany(VerumTugas::class, 'kelas_id');
    }

    public function postingan()
    {
        return $this->hasMany(VerumPostingan::class, 'kelas_id');
    }

    public function presensi()
    {
        return $this->hasMany(VerumPresensi::class, 'kelas_id');
    }
}