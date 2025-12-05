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
        'is_meeting_active', 
    ];

    /**
     * Relasi ke Mata Kuliah
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Relasi ke Dosen
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Relasi ke Tahun Akademik
     */
    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Relasi ke Materi
     */
    public function materi()
    {
        // PERBAIKAN: Mengubah 'verum_kelas_id' menjadi 'kelas_id' sesuai struktur DB umum
        return $this->hasMany(VerumMateri::class, 'kelas_id'); 
    }

    /**
     * Relasi ke Tugas
     */
    public function tugas()
    {
        // PERBAIKAN: Mengubah 'verum_kelas_id' menjadi 'kelas_id'
        return $this->hasMany(VerumTugas::class, 'kelas_id');
    }

    /**
     * Relasi ke Postingan Forum
     */
    public function postingan()
    {
        // Sudah benar menggunakan 'kelas_id'
        return $this->hasMany(VerumPostingan::class, 'kelas_id');
    }

    /**
     * Relasi ke Presensi
     */
    public function presensi()
    {
        // PERBAIKAN: Mengubah 'verum_kelas_id' menjadi 'kelas_id'
        return $this->hasMany(VerumPresensi::class, 'kelas_id');
    }
}