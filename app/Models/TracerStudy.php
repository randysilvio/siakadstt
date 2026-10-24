<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TracerStudy extends Model
{
    use HasFactory;

    protected $table = 'tracer_studies';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_lulus',
        'status_pekerjaan',
        'waktu_tunggu_bulan',
        'nama_instansi_gereja',
        'jabatan_pekerjaan',
        'kesesuaian_bidang',
        'kisaran_pendapatan',
        'kontak_atasan_nama',
        'kontak_atasan_jabatan',
        'kontak_atasan_hp',
        'saran_kurikulum',
    ];

    protected $casts = [
        'tahun_lulus' => 'integer',
        'waktu_tunggu_bulan' => 'integer',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}