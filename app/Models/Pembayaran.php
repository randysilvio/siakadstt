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
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'tanggal_bayar',
        'bukti_bayar',
        'keterangan',
    ];

    /**
     * [UPDATE] Konversi otomatis kolom tanggal menjadi objek Carbon.
     */
    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'diverifikasi_pada' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
