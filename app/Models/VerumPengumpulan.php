<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerumPengumpulan extends Model {
    use HasFactory;
    protected $table = 'verum_pengumpulan';
    protected $fillable = ['tugas_id', 'mahasiswa_id', 'file_path', 'waktu_pengumpulan', 'nilai'];

    public function tugas() {
        return $this->belongsTo(VerumTugas::class, 'tugas_id');
    }
    public function mahasiswa() {
        return $this->belongsTo(Mahasiswa::class);
    }
}