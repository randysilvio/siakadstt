<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluasiSesi extends Model
{
    use HasFactory;

    // Mendefinisikan nama tabel secara eksplisit
    protected $table = 'evaluasi_sesi';

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'nama_sesi',
        'tahun_akademik_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    /**
     * Relasi ke model TahunAkademik.
     * Sebuah sesi evaluasi milik satu tahun akademik.
     */
    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }
}