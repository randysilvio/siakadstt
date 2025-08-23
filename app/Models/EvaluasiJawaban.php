<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluasiJawaban extends Model
{
    use HasFactory;

    // Mendefinisikan nama tabel secara eksplisit
    protected $table = 'evaluasi_jawaban';

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'evaluasi_sesi_id',
        'mahasiswa_id',
        'dosen_id',
        'mata_kuliah_id',
        'evaluasi_pertanyaan_id',
        'jawaban_skala',
        'jawaban_teks',
    ];

    // =================================================================
    // ===== PENAMBAHAN KODE BARU DIMULAI DI SINI =====
    // =================================================================

    /**
     * Relasi ke model Dosen.
     * Satu jawaban evaluasi dimiliki oleh satu dosen.
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Relasi ke model EvaluasiSesi.
     * Satu jawaban evaluasi termasuk dalam satu sesi.
     */
    public function sesi()
    {
        return $this->belongsTo(EvaluasiSesi::class, 'evaluasi_sesi_id');
    }

    // =================================================================
    // ===== PENAMBAHAN KODE BARU SELESAI DI SINI =====
    // =================================================================
}
