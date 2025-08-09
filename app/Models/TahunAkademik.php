<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TahunAkademik extends Model
{
    use HasFactory;
    protected $fillable = ['tahun', 'semester', 'is_active', 'tanggal_mulai_krs', 'tanggal_selesai_krs'];
}