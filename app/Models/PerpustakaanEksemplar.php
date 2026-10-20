<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerpustakaanEksemplar extends Model
{
    use HasFactory;

    protected $table = 'perpustakaan_eksemplars';

    protected $fillable = [
        'koleksi_id',
        'kode_barcode',
        'status_fisik',
    ];

    public function koleksi()
    {
        return $this->belongsTo(Koleksi::class, 'koleksi_id');
    }
}