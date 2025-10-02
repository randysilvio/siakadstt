<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Mahasiswa|null $mahasiswa
 */
class Pembayaran extends Model
{
    use HasFactory;
    protected $fillable = ['mahasiswa_id', 'jumlah', 'semester', 'tanggal_bayar', 'status'];

    // --- TAMBAHAN: Memberitahu Laravel untuk memperlakukan kolom ini sebagai tanggal ---
    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}