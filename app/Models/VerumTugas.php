<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerumTugas extends Model {
    use HasFactory;
    protected $table = 'verum_tugas';
    protected $fillable = ['kelas_id', 'judul', 'instruksi', 'tenggat_waktu'];
    protected $casts = ['tenggat_waktu' => 'datetime'];

    public function kelas() {
        return $this->belongsTo(VerumKelas::class, 'kelas_id');
    }
    public function pengumpulan() {
        return $this->hasMany(VerumPengumpulan::class, 'tugas_id');
    }
}