<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\ProgramStudi|null $programStudi
 * @property-read \App\Models\Dosen|null $dosenWali
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MataKuliah[] $mataKuliahs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pembayaran[] $pembayarans
 * @property int $id
 * @property string $nim
 * @property string $nama_lengkap
 * @property int $program_studi_id
 * @property string|null $status_krs
 * @property string|null $catatan_kaprodi
 */
class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nim', 'nama_lengkap', 'program_studi_id', 'user_id', 'dosen_wali_id',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'alamat',
        'nomor_telepon', 'nama_ibu_kandung', 'status_mahasiswa', 'tahun_masuk',
        'status_krs', 'catatan_kaprodi'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'mahasiswa_mata_kuliah')
                    ->withPivot('nilai', 'tahun_akademik_id')
                    ->withTimestamps();
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function dosenWali(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_wali_id');
    }

    /**
     * Menghitung Indeks Prestasi Kumulatif (IPK).
     */
    public function hitungIpk(): float
    {
        $krsLulus = $this->mataKuliahs()->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])->get();
        if ($krsLulus->isEmpty()) {
            return 0.00;
        }

        $total_bobot_sks = 0;
        $total_sks = 0;
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1];

        foreach ($krsLulus as $mk) {
            $sks = $mk->sks;
            $nilai = $mk->pivot->nilai;
            if (isset($bobot_nilai[$nilai])) {
                $total_sks += $sks;
                $total_bobot_sks += ($bobot_nilai[$nilai] * $sks);
            }
        }

        return ($total_sks > 0) ? round($total_bobot_sks / $total_sks, 2) : 0.00;
    }

    /**
     * Menghitung total SKS yang sudah lulus.
     */
    public function totalSksLulus(): int
    {
        return $this->mataKuliahs()
            ->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])
            ->sum('sks');
    }

    /**
     * Menghitung Indeks Prestasi Semester (IPS) untuk tahun akademik tertentu.
     */
    public function hitungIps(int $tahunAkademikId): array
    {
        $krsSemester = $this->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $tahunAkademikId)
            ->wherePivotNotNull('nilai')
            ->get();

        if ($krsSemester->isEmpty()) {
            return ['ips' => 0.00, 'total_sks' => 0, 'nilaiBobot' => []];
        }

        $total_sks_semester = 0;
        $total_bobot_sks_semester = 0;
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];
        $nilaiBobot = [];

        foreach ($krsSemester as $mk) {
            $sks = $mk->sks;
            $nilai = $mk->pivot->nilai;

            if (isset($bobot_nilai[$nilai])) {
                $total_sks_semester += $sks;
                $bobot = $bobot_nilai[$nilai] * $sks;
                $total_bobot_sks_semester += $bobot;
                $nilaiBobot[$mk->id] = $bobot;
            }
        }

        $ips = ($total_sks_semester > 0) ? round($total_bobot_sks_semester / $total_sks_semester, 2) : 0.00;

        return [
            'ips' => $ips,
            'total_sks' => $total_sks_semester,
            'nilaiBobot' => $nilaiBobot,
        ];
    }
}
