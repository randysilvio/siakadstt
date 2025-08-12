<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerumMateri extends Model {
    use HasFactory;
    protected $table = 'verum_materi';
    protected $fillable = ['kelas_id', 'judul', 'deskripsi', 'file_path', 'link_url'];

    public function kelas() {
        return $this->belongsTo(VerumKelas::class, 'kelas_id');
    }
}