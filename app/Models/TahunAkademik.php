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

    public function getNamaSemesterAttribute(): string
    {
        return trim(($this->tahun ?? '') . ' - Semester ' . strtoupper($this->semester ?? ''));
    }
}