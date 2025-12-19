<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayarans';

    protected $fillable = [
        'user_id',        // [TAMBAHAN BARU]
        'mahasiswa_id',   // Ini sekarang boleh null
        'semester',
        'jenis_pembayaran',
        'jumlah',
        'status',
        'tanggal_bayar',
        'bukti_bayar',
        'keterangan',
    ];

    // Relasi Lama (Tetap pertahankan untuk backward compatibility)
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    // [TAMBAHAN BARU] Relasi ke User (untuk Camaba)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}