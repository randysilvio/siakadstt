<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BimbinganLog extends Model
{
    use HasFactory;

    protected $table = 'bimbingan_logs';

    protected $fillable = [
        'bimbingan_id',
        'dosen_id',
        'tanggal_bimbingan',
        'topik_bab',
        'catatan_mahasiswa',
        'arahan_dosen',
        'file_draf',
        'status_paraf',
    ];

    protected $casts = [
        'tanggal_bimbingan' => 'date',
    ];

    public function bimbingan()
    {
        return $this->belongsTo(BimbinganTugasAkhir::class, 'bimbingan_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}