<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- TAMBAHKAN INI

class TahunAkademik extends Model
{
    use HasFactory, SoftDeletes; // <-- TAMBAHKAN SoftDeletes

    protected $fillable = [
        'tahun', 
        'semester', 
        'is_active', 
        'tanggal_mulai_krs', 
        'tanggal_selesai_krs'
    ];
}
