<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmbDocument extends Model
{
    use HasFactory;

    protected $table = 'pmb_documents';

    protected $fillable = [
        'camaba_id',
        'jenis_dokumen',
        'path_file',
        'status_validasi',
        'catatan_admin'
    ];

    public function camaba()
    {
        return $this->belongsTo(Camaba::class);
    }
}