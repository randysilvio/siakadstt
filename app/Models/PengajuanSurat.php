<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanSurat extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_surats';

    protected $fillable = [
        'mahasiswa_id',
        'jenis_surat',
        'keperluan',
        'tujuan_instansi',
        'nomor_surat',
        'status',
        'catatan_admin',
        'kode_verifikasi',
        'tanggal_disetujui',
    ];

    protected $casts = [
        'tanggal_disetujui' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}