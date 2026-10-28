<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TahunAkademik extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tahun', 
        'semester', 
        'is_active', 
        'tanggal_mulai_krs', 
        'tanggal_selesai_krs'
    ];

    protected $appends = ['nama_semester'];

    /** Label baku periode tagihan keuangan, mis. "2026/2027 Ganjil". */
    public function getLabelTagihanAttribute(): string
    {
        return trim(($this->tahun ?? '') . ' ' . ucfirst(strtolower($this->semester ?? '')));
    }

    /** Pilihan periode tagihan (terbaru dahulu) untuk formulir Keuangan. */
    public static function pilihanTagihan(): \Illuminate\Support\Collection
    {
        return static::orderBy('tahun', 'desc')->orderBy('semester', 'desc')->get();
    }

    public function getNamaSemesterAttribute(): string
    {
        return trim(($this->tahun ?? '') . ' - Semester ' . strtoupper($this->semester ?? ''));
    }
}