<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiPegawai extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'absensi_pegawai';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'lokasi_kerja_id',
        'tanggal_absensi',
        'waktu_check_in',
        'latitude_check_in',
        'longitude_check_in',
        'foto_check_in',
        'waktu_check_out',
        'latitude_check_out',
        'longitude_check_out',
        'foto_check_out',
        'status_kehadiran',
        'keterangan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_absensi' => 'date',
        'waktu_check_in' => 'datetime',
        'waktu_check_out' => 'datetime',
    ];

    /**
     * Mendapatkan data user yang melakukan absensi.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan data lokasi kerja tempat absensi dilakukan.
     */
    public function lokasiKerja()
    {
        return $this->belongsTo(LokasiKerja::class);
    }
}