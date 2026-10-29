<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    use \App\Models\Concerns\TercatatAktivitas;

    public function labelAktivitas(): string
    {
        return 'kurikulum ' . $this->nama_kurikulum;
    }

    use HasFactory;

    // [UPDATE DISINI] Tambahkan 'program_studi_id'
    protected $fillable = ['nama_kurikulum', 'tahun', 'is_active', 'program_studi_id'];

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function mataKuliahs()
    {
        return $this->hasMany(MataKuliah::class);
    }
}