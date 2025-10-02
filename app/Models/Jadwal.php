<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * --- BLOK BARU DITAMBAHKAN ---
 * @property-read \App\Models\MataKuliah|null $mataKuliah
 */
class Jadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'mata_kuliah_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }
}