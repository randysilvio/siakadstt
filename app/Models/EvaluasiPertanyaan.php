<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluasiPertanyaan extends Model
{
    use HasFactory;

    // Mendefinisikan nama tabel secara eksplisit
    protected $table = 'evaluasi_pertanyaan';

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'pertanyaan',
        'tipe_jawaban',
        'is_active',
        'urutan',
    ];
}