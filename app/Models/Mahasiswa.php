<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\ProgramStudi|null $programStudi
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Dosen|null $dosenWali
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pembayaran[] $pembayarans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MataKuliah[] $mataKuliahs
 */
class Mahasiswa extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nim',
        'nama_lengkap',
        'program_studi_id',
        'user_id',
        'dosen_wali_id',
        // Tambahan
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'nomor_telepon',
        'nama_ibu_kandung',
        'status_mahasiswa',
        'tahun_masuk',
    ];

    /**
     * Relasi ke ProgramStudi (satu mahasiswa punya satu prodi).
     */
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    /**
     * Relasi ke MataKuliah (satu mahasiswa bisa mengambil banyak mata kuliah).
     */
    public function mataKuliahs()
    {
        // PERBAIKAN: Menambahkan 'tahun_akademik_id' ke dalam withPivot
        return $this->belongsToMany(MataKuliah::class, 'mahasiswa_mata_kuliah')
                    ->withPivot('nilai', 'tahun_akademik_id')
                    ->withTimestamps();
    }

    /**
     * Relasi ke User (satu mahasiswa dimiliki oleh satu user).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Pembayaran (satu mahasiswa bisa memiliki banyak riwayat pembayaran).
     */
    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Relasi ke Dosen (satu mahasiswa punya satu dosen wali).
     */
    public function dosenWali()
    {
        return $this->belongsTo(Dosen::class, 'dosen_wali_id');
    }
}
