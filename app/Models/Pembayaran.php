<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayarans';

    protected $fillable = [
        'user_id',
        'mahasiswa_id',
        'semester',
        'jenis_pembayaran',
        'jumlah',
        'status',
        'tanggal_bayar',
        'bukti_bayar',
        'keterangan',
    ];

    /**
     * [UPDATE] Konversi otomatis kolom tanggal menjadi objek Carbon.
     */
    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}