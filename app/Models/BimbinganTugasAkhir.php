<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BimbinganTugasAkhir extends Model
{
    use HasFactory;

    protected $table = 'bimbingan_tugas_akhirs';

    protected $fillable = [
        'mahasiswa_id',
        'jenis_bimbingan',
        'judul',
        'lokasi_pelayanan',
        'abstrak_ringkas',
        'pembimbing_1_id',
        'pembimbing_2_id',
        'status',
        'tanggal_sidang',
        'ruang_sidang',
        'nilai_angka',
        'nilai_huruf',
        'catatan_kaprodi',
    ];

    protected $casts = [
        'tanggal_sidang' => 'date',
        'nilai_angka' => 'float',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function pembimbing1()
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_1_id');
    }

    public function pembimbing2()
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_2_id');
    }

    public function logs()
    {
        return $this->hasMany(BimbinganLog::class, 'bimbingan_id')->orderBy('tanggal_bimbingan', 'asc')->orderBy('id', 'asc');
    }
}