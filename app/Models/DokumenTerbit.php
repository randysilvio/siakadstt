<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DokumenTerbit extends Model
{
    protected $table = 'dokumen_terbits';

    protected $fillable = ['kode', 'jenis', 'mahasiswa_id', 'ringkasan', 'sidik', 'diterbitkan_oleh'];

    protected $casts = [
        'ringkasan' => 'array',
    ];

    public const JUDUL = [
        'KRS'        => 'KARTU RENCANA STUDI (KRS) AKADEMIK',
        'KHS'        => 'KARTU HASIL STUDI (KHS) SEMESTER',
        'TRANSKRIP'  => 'TRANSKRIP NILAI AKADEMIK RESMI',
        'SURATAKTIF' => 'SURAT KETERANGAN AKTIF KULIAH',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Mencatat dokumen yang dicetak beserta ringkasan isinya dan mengembalikan record dengan kode verifikasi baru.
     * Cetakan berulang dengan isi identik memakai kode yang sama agar tidak menumpuk.
     */
    public static function terbitkan(string $jenis, Mahasiswa $mahasiswa, array $ringkasan): self
    {
        $sidik = hash('sha256', json_encode($ringkasan, JSON_UNESCAPED_UNICODE));

        $ada = static::where('jenis', $jenis)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('sidik', $sidik)
            ->first();
        if ($ada) {
            return $ada;
        }

        do {
            $kode = $jenis . '-' . strtoupper(Str::random(12));
        } while (static::where('kode', $kode)->exists());

        return static::create([
            'kode'             => $kode,
            'jenis'            => $jenis,
            'mahasiswa_id'     => $mahasiswa->id,
            'ringkasan'        => $ringkasan,
            'sidik'            => $sidik,
            'diterbitkan_oleh' => Auth::id(),
        ]);
    }

    /** Memastikan ringkasan yang tersimpan belum diubah sejak diterbitkan. */
    public function utuh(): bool
    {
        return hash_equals($this->sidik, hash('sha256', json_encode($this->ringkasan, JSON_UNESCAPED_UNICODE)));
    }
}
