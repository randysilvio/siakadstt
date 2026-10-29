<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use \App\Models\Concerns\TercatatAktivitas;

    public function labelAktivitas(): string
    {
        return 'jadwal ' . optional($this->mataKuliah)->kode_mk . ' kelas ' . $this->nama_kelas . ' (' . $this->hari . ')';
    }

    use HasFactory;

    protected $fillable = [
        'mata_kuliah_id',
        'nama_kelas',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruang',
        'kuota',
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }
}