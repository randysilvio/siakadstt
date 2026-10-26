<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nim', 'nama_lengkap', 'program_studi_id', 'user_id', 'dosen_wali_id',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'alamat',
        'nomor_telepon', 'nama_ibu_kandung', 'status_mahasiswa', 'tanggal_lulus', 'tahun_masuk',
        'status_krs', 'catatan_kaprodi', 'foto_profil', 'jalur_pendaftaran',
        'nik', 'nisn', 'kewarganegaraan', 'dusun', 'rt', 'rw', 'kelurahan', 
        'kecamatan', 'kode_pos', 'jenis_tinggal', 'alat_transportasi', 
        'nik_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu', 
        'nama_ayah', 'nik_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 
        'penghasilan_ayah', 'nama_wali', 'pekerjaan_wali'
    ];
    
    protected $appends = ['foto_profil_url'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_lulus' => 'date',
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
                    ->withPivot(
                        'nama_kelas',
                        'nilai',
                        'tahun_akademik_id',
                        'nilai_kehadiran',
                        'nilai_tugas',
                        'nilai_uts',
                        'nilai_uas',
                        'nilai_akhir_angka'
                    )
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

    public function tracerStudy(): HasOne
    {
        return $this->hasOne(TracerStudy::class, 'mahasiswa_id');
    }

    public function getSudahIsiTracerAttribute(): bool
    {
        return $this->tracerStudy()->exists();
    }

    public function getFotoProfilUrlAttribute()
    {
        if ($this->foto_profil) {
            return asset('storage/' . $this->foto_profil);
        }
        return asset('images/default-student.png');
    }

    public function getAngkatanAttribute()
    {
        return $this->attributes['tahun_masuk'] ?? null;
    }

    public function getStatusEvaluasiAttribute(): string
    {
        if ($this->status_mahasiswa === 'Lulus') {
            return 'sudah_isi';
        }

        $sesiAktif = EvaluasiSesi::where('is_active', true)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->first();

        if (!$sesiAktif || !$sesiAktif->tahun_akademik_id || $this->status_krs !== 'Disetujui') {
            return 'sudah_isi';
        }

        $mataKuliahWajibIds = $this->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $sesiAktif->tahun_akademik_id)
            ->pluck('mata_kuliahs.id')
            ->toArray();

        if (empty($mataKuliahWajibIds)) {
            return 'sudah_isi';
        }

        $evaluasiSelesaiIds = EvaluasiJawaban::where('mahasiswa_id', $this->id)
            ->where('evaluasi_sesi_id', $sesiAktif->id)
            ->distinct()
            ->pluck('mata_kuliah_id')
            ->toArray();

        $belumDievaluasi = array_diff($mataKuliahWajibIds, $evaluasiSelesaiIds);

        return !empty($belumDievaluasi) ? 'belum_isi' : 'sudah_isi';
    }

    public function hitungIpk(): float
    {
        $krs_selesai = $this->mataKuliahs()->wherePivotNotNull('nilai')->get();

        if ($krs_selesai->isEmpty()) {
            return 0.00;
        }

        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];
        $matkulTerbaik = [];

        foreach ($krs_selesai as $mk) {
            $bobotSekarang = $bobot_nilai[$mk->pivot->nilai] ?? 0;
            if (isset($matkulTerbaik[$mk->id])) {
                $bobotTersimpan = $bobot_nilai[$matkulTerbaik[$mk->id]['nilai']] ?? 0;
                if ($bobotSekarang > $bobotTersimpan) {
                    $matkulTerbaik[$mk->id] = ['sks' => $mk->sks, 'nilai' => $mk->pivot->nilai];
                }
            } else {
                $matkulTerbaik[$mk->id] = ['sks' => $mk->sks, 'nilai' => $mk->pivot->nilai];
            }
        }

        $total_sks_diambil = 0;
        $total_bobot_sks = 0;

        foreach ($matkulTerbaik as $mk) {
            $total_sks_diambil += $mk['sks'];
            $bobot = $bobot_nilai[$mk['nilai']] ?? 0;
            $total_bobot_sks += ($bobot * $mk['sks']);
        }

        return ($total_sks_diambil > 0) ? round($total_bobot_sks / $total_sks_diambil, 2) : 0.00;
    }

    public function totalSksLulus(): int
    {
        $krs_selesai = $this->mataKuliahs()->wherePivotNotNull('nilai')->get();
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];
        $matkulTerbaik = [];

        foreach ($krs_selesai as $mk) {
            $bobotSekarang = $bobot_nilai[$mk->pivot->nilai] ?? -1;
            if (isset($matkulTerbaik[$mk->id])) {
                $bobotTersimpan = $bobot_nilai[$matkulTerbaik[$mk->id]['nilai']] ?? -1;
                if ($bobotSekarang > $bobotTersimpan) {
                    $matkulTerbaik[$mk->id] = ['sks' => $mk->sks, 'nilai' => $mk->pivot->nilai];
                }
            } else {
                $matkulTerbaik[$mk->id] = ['sks' => $mk->sks, 'nilai' => $mk->pivot->nilai];
            }
        }

        $total_lulus = 0;
        foreach ($matkulTerbaik as $mk) {
            if (in_array($mk['nilai'], ['A', 'B', 'C', 'D'])) {
                $total_lulus += $mk['sks'];
            }
        }
        
        return $total_lulus;
    }

    public function hitungIps($tahun_akademik_id)
    {
        $krs = $this->mataKuliahs()
                    ->wherePivot('tahun_akademik_id', $tahun_akademik_id)
                    ->wherePivotNotNull('nilai')
                    ->get();

        $total_sks_diambil = 0;
        $total_bobot_sks = 0;
        $nilaiBobot = [];
        
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

        foreach ($krs as $mk) {
            $sks = $mk->sks;
            $nilai = $mk->pivot->nilai;
            $bobot = $bobot_nilai[$nilai] ?? 0;
            
            $sub_total = $sks * $bobot;

            $total_sks_diambil += $sks;
            $total_bobot_sks += $sub_total;
            
            $nilaiBobot[$mk->id] = $sub_total;
        }

        $ips = ($total_sks_diambil > 0) ? round($total_bobot_sks / $total_sks_diambil, 2) : 0;

        return [
            'ips' => $ips,
            'total_sks' => $total_sks_diambil,
            'nilaiBobot' => $nilaiBobot
        ];
    }
}