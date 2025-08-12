<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerumPostingan extends Model {
    use HasFactory;
    protected $table = 'verum_postingan';
    protected $fillable = ['kelas_id', 'user_id', 'konten'];

    public function kelas() {
        return $this->belongsTo(VerumKelas::class, 'kelas_id');
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}