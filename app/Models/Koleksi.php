<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Koleksi extends Model
{
    use HasFactory;

    protected $table = 'perpustakaan_koleksi';

    protected $fillable = [
        'judul',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'isbn',
        'jumlah_stok',
        'jumlah_tersedia',
        'lokasi_rak',
        'sinopsis',
        'gambar_sampul',
    ];

    // [TAMBAHAN FASE 2] Relasi ke Eksemplar Fisik
    public function eksemplars()
    {
        return $this->hasMany(PerpustakaanEksemplar::class, 'koleksi_id');
    }

    /**
     * Riwayat peminjaman judul ini (dipakai dashboard perpustakaan: koleksi populer).
     */
    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class, 'koleksi_id');
    }
}
