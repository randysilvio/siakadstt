<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nim', 'nama_lengkap', 'program_studi_id', 'user_id', 'dosen_wali_id',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'alamat',
        'nomor_telepon', 'nama_ibu_kandung', 'status_mahasiswa', 'tahun_masuk',
        'status_krs', 'catatan_kaprodi', 'foto_profil'
    ];
    
    // Menambahkan atribut virtual
    protected $appends = ['foto_profil_url'];

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
     * [PERBAIKAN] Menggunakan asset lokal untuk privasi.
     * Tidak lagi mengirim nama mahasiswa ke server pihak ketiga (ui-avatars).
     */
    public function getFotoProfilUrlAttribute()
    {
        if ($this->foto_profil) {
            return asset('storage/' . $this->foto_profil);
        }
        // Pastikan Anda punya file ini di folder public/images/
        return asset('images/default-student.png');
    }

    /**
     * Menghitung IPK (Indeks Prestasi Kumulatif) - Semua Semester
     */
    public function hitungIpk(): float
    {
        $krsLulus = $this->mataKuliahs()->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])->get();
        if ($krsLulus->isEmpty()) {
            return 0.00;
        }

        $total_bobot_sks = 0;
        $total_sks = 0;
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

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
     * Menghitung Total SKS yang sudah lulus
     */
    public function totalSksLulus(): int
    {
        return $this->mataKuliahs()
            ->wherePivotIn('nilai', ['A', 'B', 'C', 'D'])
            ->sum('sks');
    }

    /**
     * [BARU] Menghitung IPS (Indeks Prestasi Semester)
     * Mengembalikan Array lengkap agar kompatibel dengan view KHS
     */
    public function hitungIps($tahun_akademik_id)
    {
        // 1. Ambil matkul di semester tertentu yang sudah dinilai
        $krs = $this->mataKuliahs()
                    ->wherePivot('tahun_akademik_id', $tahun_akademik_id)
                    ->wherePivotNotNull('nilai')
                    ->get();

        $total_sks = 0;
        $total_bobot_sks = 0;
        $nilaiBobot = []; // Menyimpan bobot per matkul (misal: 3 SKS * 4 = 12)
        
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

        foreach ($krs as $mk) {
            $nilai = $mk->pivot->nilai;
            $sks = $mk->sks;

            if (isset($bobot_nilai[$nilai])) {
                $bobot = $bobot_nilai[$nilai];
                $sub_total = $sks * $bobot;

                $total_sks += $sks;
                $total_bobot_sks += $sub_total;
                
                // Simpan untuk ditampilkan di tabel
                $nilaiBobot[$mk->id] = $sub_total;
            }
        }

        $ips = ($total_sks > 0) ? round($total_bobot_sks / $total_sks, 2) : 0;

        // Mengembalikan array agar view index.blade.php tidak error
        return [
            'ips' => $ips,
            'total_sks' => $total_sks,
            'nilaiBobot' => $nilaiBobot
        ];
    }
}