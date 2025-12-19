<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmbPeriod extends Model
{
    use HasFactory;

    protected $table = 'pmb_periods';

    protected $fillable = [
        'nama_gelombang',
        'tanggal_buka',
        'tanggal_tutup',
        'biaya_pendaftaran',
        'is_active',
        // [BARU] Tambahkan kolom ini agar bisa disimpan
        'tanggal_ujian',
        'jam_mulai_ujian',
        'jam_selesai_ujian',
        'jenis_ujian',
        'lokasi_ujian',
    ];

    protected $casts = [
        'tanggal_buka' => 'date',
        'tanggal_tutup' => 'date',
        'tanggal_ujian' => 'date', // Cast tanggal ujian juga
        'is_active' => 'boolean',
    ];

    // Relasi: Satu periode punya banyak pendaftar
    public function camabas()
    {
        return $this->hasMany(Camaba::class);
    }
}