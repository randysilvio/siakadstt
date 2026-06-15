<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeputusan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Memastikan data JSON langsung dikonversi menjadi Array saat ditarik dari database
    protected $casts = [
        'tanggal_terbit' => 'date',
        'menimbang' => 'array',
        'mengingat' => 'array',
        'memperhatikan' => 'array',
        'menetapkan' => 'array',
        'tembusan' => 'array',
        'panitia_lainnya' => 'array', // [TAMBAHAN BARU]
    ];

    // Relasi ke Dosen (Otomatis menarik jabatan panitia dari tabel pivot)
    public function dosens()
    {
        return $this->belongsToMany(Dosen::class, 'dosen_surat_keputusan')
                    ->withPivot('jabatan_dalam_surat')
                    ->withTimestamps();
    }
}