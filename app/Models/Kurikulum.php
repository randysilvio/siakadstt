<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    use HasFactory;

    // Kode lama tetap aman
    protected $fillable = ['nama_kurikulum', 'tahun', 'is_active'];

    /**
     * [TAMBAHAN BARU]
     * Fungsi ini diperlukan agar sistem bisa membaca Nama Prodi dari Kurikulum.
     * Tanpa ini, fitur Cetak Jadwal akan error.
     */
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    // Kode lama tetap aman
    public function mataKuliahs()
    {
        return $this->hasMany(MataKuliah::class);
    }
}