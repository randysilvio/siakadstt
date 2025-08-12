<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerumKehadiran extends Model {
    use HasFactory;
    protected $table = 'verum_kehadiran';
    protected $fillable = ['presensi_id', 'mahasiswa_id', 'status', 'waktu_absen'];

    public function presensi() {
        return $this->belongsTo(VerumPresensi::class, 'presensi_id');
    }
    public function mahasiswa() {
        return $this->belongsTo(Mahasiswa::class);
    }
}