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

    // Semua kolom PDDikti sudah didaftarkan penuh + tanggal_lulus
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
    
    // Menambahkan atribut virtual
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
     * Menggunakan asset lokal untuk privasi.
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
     * Menghitung IPK (Indeks Prestasi Kumulatif) - Standar BAN-PT
     * Mengambil 1 nilai terbaik dari mata kuliah yang sama dan menghitung pembagi dari semua SKS yang diambil.
     */
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

    /**
     * Menghitung Total SKS yang sudah lulus (Nilai A, B, C, D)
     * Mengabaikan SKS ganda jika mata kuliah diulang.
     */
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

    /**
     * Menghitung IPS (Indeks Prestasi Semester)
     * Mengembalikan Array lengkap agar kompatibel dengan view KHS
     */
    public function hitungIps($tahun_akademik_id)
    {
        // Ambil matkul di semester tertentu yang sudah dinilai
        $krs = $this->mataKuliahs()
                    ->wherePivot('tahun_akademik_id', $tahun_akademik_id)
                    ->wherePivotNotNull('nilai')
                    ->get();

        $total_sks_diambil = 0;
        $total_bobot_sks = 0;
        $nilaiBobot = []; // Menyimpan bobot per matkul (misal: 3 SKS * 4 = 12)
        
        $bobot_nilai = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];

        foreach ($krs as $mk) {
            $sks = $mk->sks;
            $nilai = $mk->pivot->nilai;
            $bobot = $bobot_nilai[$nilai] ?? 0;
            
            $sub_total = $sks * $bobot;

            // Semua SKS masuk ke pembagi
            $total_sks_diambil += $sks;
            $total_bobot_sks += $sub_total;
            
            // Simpan untuk ditampilkan di tabel
            $nilaiBobot[$mk->id] = $sub_total;
        }

        $ips = ($total_sks_diambil > 0) ? round($total_bobot_sks / $total_sks_diambil, 2) : 0;

        // Mengembalikan array agar view index.blade.php tidak error
        return [
            'ips' => $ips,
            'total_sks' => $total_sks_diambil,
            'nilaiBobot' => $nilaiBobot
        ];
    }
}