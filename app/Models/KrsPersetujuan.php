<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Status persetujuan KRS seorang mahasiswa pada satu tahun akademik.
 * Kolom mahasiswas.status_krs hanyalah salinan untuk semester yang sedang aktif.
 */
class KrsPersetujuan extends Model
{
    use \App\Models\Concerns\TercatatAktivitas;

    /** Kolom yang tidak perlu masuk log aktivitas. */
    protected array $kolomTidakDicatat = ['catatan'];

    public function harusDicatat(string $aksi): bool
    {
        if ($aksi === 'hapus') {
            return false;
        }
        $lama = $this->getOriginal('status');
        return in_array($this->status, [self::DISETUJUI, self::DITOLAK], true)
            || ($aksi === 'ubah' && $lama === self::DISETUJUI);
    }

    public function labelAktivitas(): string
    {
        return 'status KRS ' . optional($this->mahasiswa)->nim . ' · ' . optional($this->mahasiswa)->nama_lengkap . ' → ' . $this->status;
    }

    public const BELUM = 'Belum Mengajukan';
    public const MENUNGGU = 'Menunggu Persetujuan';
    public const DISETUJUI = 'Disetujui';
    public const DITOLAK = 'Ditolak';

    protected $table = 'krs_persetujuans';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'status',
        'catatan',
        'divalidasi_oleh',
        'divalidasi_pada',
    ];

    protected $casts = [
        'divalidasi_pada' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh');
    }

    /**
     * Subquery id mahasiswa yang KRS-nya disetujui pada tahun akademik tertentu (untuk whereIn).
     */
    public static function idsDisetujui(int $tahunAkademikId)
    {
        return static::query()
            ->select('mahasiswa_id')
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->where('status', self::DISETUJUI);
    }
}
