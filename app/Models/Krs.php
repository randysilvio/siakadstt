<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Krs extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'krs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'tahun_akademik_id',
        'nilai_huruf',
        'nilai_angka',
        'status_krs',
        'tanggal_validasi',
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