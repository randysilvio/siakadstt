<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosenTridharma extends Model
{
    use HasFactory;

    protected $table = 'dosen_tridharmas';

    protected $fillable = [
        'dosen_id',
        'unsur',
        'kategori',
        'judul',
        'peran',
        'tahun',
        'semester',
        'sks_bkd',
        'angka_kredit',
        'nama_jurnal_penerbit_lokasi',
        'nomor_identitas',
        'tautan_eksternal',
        'file_bukti',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'sks_bkd' => 'float',
        'angka_kredit' => 'float',
    ];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }
}