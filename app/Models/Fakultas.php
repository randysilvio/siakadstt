<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    use HasFactory;

    protected $table = 'fakultas';

    protected $fillable = [
        'kode_fakultas',
        'nama_fakultas',
        'dekan_dosen_id',
    ];

    public function dekan()
    {
        return $this->belongsTo(Dosen::class, 'dekan_dosen_id');
    }

    public function programStudis()
    {
        return $this->hasMany(ProgramStudi::class, 'fakultas_id');
    }
}