<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camaba extends Model
{
    use HasFactory;

    protected $table = 'camabas';

    protected $fillable = [
        'user_id',
        'pmb_period_id',
        'no_pendaftaran',
        'pilihan_prodi_1_id',
        'pilihan_prodi_2_id',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'no_hp',
        'alamat',
        'sekolah_asal',
        'nisn',
        'tahun_lulus',
        'nilai_rata_rata_rapor',
        'status_pendaftaran',
        'is_migrated',
    ];

    // Relasi ke User (Akun Login)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Periode PMB
    public function period()
    {
        return $this->belongsTo(PmbPeriod::class, 'pmb_period_id');
    }

    // Relasi ke Prodi Pilihan
    public function prodi1()
    {
        return $this->belongsTo(ProgramStudi::class, 'pilihan_prodi_1_id');
    }

    public function prodi2()
    {
        return $this->belongsTo(ProgramStudi::class, 'pilihan_prodi_2_id');
    }

    // Relasi ke Dokumen Upload
    public function documents()
    {
        return $this->hasMany(PmbDocument::class);
    }
}