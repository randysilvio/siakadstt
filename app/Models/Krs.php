<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Krs extends Model
{
    use HasFactory;
    
    /**
     * [PERBAIKAN FASE 1] Menunjuk ke tabel pivot mahasiswa_mata_kuliah yang sebenarnya ada di DB.
     *
     * @var string
     */
    protected $table = 'mahasiswa_mata_kuliah';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'tahun_akademik_id',
        'nilai',
    ];

    /**
     * Get the mahasiswa that owns the Krs.
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Get the mata kuliah that owns the Krs.
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }

    /**
     * Get the tahun akademik that owns the Krs.
     */
    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }
}