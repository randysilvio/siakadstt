<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayarans';

    protected $fillable = [
        'user_id',
        'mahasiswa_id',
        'semester',
        'jenis_pembayaran',
        'jumlah',
        'status',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'tanggal_bayar',
        'bukti_bayar',
        'keterangan',
    ];

    /**
     * [UPDATE] Konversi otomatis kolom tanggal menjadi objek Carbon.
     */
    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'diverifikasi_pada' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * Tagihan milik satu semester akademik. Label baku "2026/2027 Ganjil"; label lama yang diketik bebas
     * ("GANJIL 2026/2027", "Gasal 2026/2027", "2026/2027 - Semester GANJIL") tetap dikenali.
     */
    public function scopeUntukSemester($query, TahunAkademik $ta)
    {
        $smt = [mb_strtolower($ta->semester)];
        if (in_array($smt[0], ['ganjil', 'gasal'], true)) {
            $smt = ['ganjil', 'gasal'];
        }
        $varian = [];
        foreach ($smt as $s) {
            array_push($varian, "{$ta->tahun} {$s}", "{$s} {$ta->tahun}", "{$ta->tahun} - semester {$s}", "semester {$s} {$ta->tahun}");
        }

        return $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(semester))'), $varian);
    }
}
