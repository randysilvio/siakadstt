<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerumKelas extends Model {
    use HasFactory;
    protected $table = 'verum_kelas';
    protected $fillable = ['mata_kuliah_id', 'dosen_id', 'tahun_akademik_id', 'nama_kelas', 'deskripsi', 'kode_kelas'];

    public function mataKuliah() {
        return $this->belongsTo(MataKuliah::class);
    }
    public function dosen() {
        return $this->belongsTo(Dosen::class);
    }
    public function tahunAkademik() {
        return $this->belongsTo(TahunAkademik::class);
    }
    public function materi() {
        return $this->hasMany(VerumMateri::class, 'kelas_id');
    }
    public function tugas() {
        return $this->hasMany(VerumTugas::class, 'kelas_id');
    }
    
    /**
     * Relasi ke Postingan Forum.
     */
    public function postingan() {
        return $this->hasMany(VerumPostingan::class, 'kelas_id')->latest();
    }

    /**
     * Mendefinisikan relasi ke sesi presensi.
     */
    public function presensi() {
        return $this->hasMany(VerumPresensi::class, 'kelas_id');
    }
}
