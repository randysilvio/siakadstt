<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Keanggotaan panitia PMB untuk satu tahun penerimaan.
 * Akses panitia aktif selama `ditutup_pada` masih kosong.
 */
class PmbKepanitiaan extends Model
{
    protected $table = 'pmb_kepanitiaans';

    protected $fillable = ['tahun_pmb', 'user_id', 'jabatan', 'ditunjuk_oleh', 'ditutup_pada'];

    protected $casts = [
        'ditutup_pada' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function penunjuk()
    {
        return $this->belongsTo(User::class, 'ditunjuk_oleh');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereNull('ditutup_pada');
    }
}
