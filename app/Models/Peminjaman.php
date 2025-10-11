<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'peminjamans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'koleksi_id',
        'user_id',
        'tanggal_pinjam',
        'jatuh_tempo',
        'tanggal_kembali',
        'status',
    ];

    /**
     * Mendapatkan data buku (koleksi) yang dipinjam.
     */
    public function koleksi(): BelongsTo
    {
        return $this->belongsTo(Koleksi::class, 'koleksi_id');
    }

    /**
     * Mendapatkan data pengguna yang meminjam.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}