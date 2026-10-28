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

    /** Status akademik baku (dipakai validasi form & impor). */
    public const STATUS = ['Aktif', 'Cuti', 'Lulus', 'Drop Out', 'Non-Aktif', 'Keluar'];

    /** Mengembalikan status baku yang cocok (tanpa membedakan huruf besar/kecil), atau null. */
    public static function normalisasiStatus(?string $status): ?string
    {
        $cari = mb_strtolower(trim((string) $status));
        foreach (self::STATUS as $baku) {
            if (mb_strtolower($baku) === $cari) {
                return $baku;
            }
        }
        return null;
    }

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
                        'nilai_akhir_angka',
                        'nilai_manual'
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
        return $this->statusEvaluasiCache ??= $this->hitungStatusEvaluasi();
    }

    private function hitungStatusEvaluasi(): string
    {
        if ($this->status_mahasiswa === 'Lulus') {
            return 'sudah_isi';
        }

        $sesiAktif = EvaluasiSesi::where('is_active', true)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->orderByDesc('tanggal_mulai')
            ->first();

        // Kewajiban EDOM mengikuti KRS yang disetujui pada semester sesi evaluasi (bukan status semester berjalan),
        // sehingga evaluasi semester lalu tetap wajib walaupun semester baru sudah diaktifkan.
        if (!$sesiAktif || !$sesiAktif->tahun_akademik_id || !$this->krsDisetujuiPada((int) $sesiAktif->tahun_akademik_id)) {
            return 'sudah_isi';
        }

        // Hanya hitung mata kuliah yang sudah memiliki dosen pengampu agar tidak terjadi deadlock evaluasi
        $mataKuliahWajibIds = $this->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $sesiAktif->tahun_akademik_id)
            ->where(fn ($q) => $q->whereNotNull('mata_kuliahs.dosen_id')->orWhereHas('teamDosens'))
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

    /** Rekap IPK & SKS yang sudah dihitung (per instance), agar tidak dihitung ulang dalam satu request. */
    protected ?array $rekapNilaiCache = null;

    /** Hasil EDOM yang sudah dihitung (per instance). */
    protected ?string $statusEvaluasiCache = null;

    /**
     * Mengisi rekap dari perhitungan massal (RekapNilaiService::rekapBanyak) untuk menghindari N+1 query.
     */
    public function setRekapNilai(array $rekap): static
    {
        $this->rekapNilaiCache = $rekap;
        return $this;
    }

    /**
     * Mengisi rekap IPK/SKS untuk sekumpulan mahasiswa dengan satu query (hindari N+1 di laporan & daftar).
     */
    public static function isiRekapNilai(\Illuminate\Support\Collection $mahasiswas): \Illuminate\Support\Collection
    {
        $rekap = app(\App\Services\RekapNilaiService::class)->rekapBanyak($mahasiswas->pluck('id'));
        foreach ($mahasiswas as $mhs) {
            $mhs->setRekapNilai($rekap[$mhs->id]);
        }
        return $mahasiswas;
    }

    public function rekapNilai(): array
    {
        return $this->rekapNilaiCache ??= app(\App\Services\RekapNilaiService::class)->rekap($this->id);
    }

    /** IPK kumulatif (nilai terbaik untuk mata kuliah yang diulang). */
    public function hitungIpk(): float
    {
        return (float) $this->rekapNilai()['ipk'];
    }

    /** Total SKS lulus (nilai terbaik A–D). */
    public function totalSksLulus(): int
    {
        return (int) $this->rekapNilai()['sks_lulus'];
    }

    /** Apakah mahasiswa sudah memiliki minimal satu nilai (untuk EWS & batas SKS mahasiswa baru). */
    public function sudahPunyaNilai(): bool
    {
        return $this->rekapNilai()['sks_bernilai'] > 0;
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

    /**
     * Alasan data mahasiswa tidak boleh dihapus (riwayat akademik/keuangan harus tetap utuh), atau null bila aman.
     */
    public function alasanTidakBisaDihapus(): ?string
    {
        $cek = [
            'riwayat KRS/nilai'       => fn () => \Illuminate\Support\Facades\DB::table('mahasiswa_mata_kuliah')->where('mahasiswa_id', $this->id)->exists(),
            'riwayat tagihan'         => fn () => $this->pembayarans()->exists(),
            'data bimbingan TA/PPL'   => fn () => \App\Models\BimbinganTugasAkhir::where('mahasiswa_id', $this->id)->exists(),
            'permohonan surat'        => fn () => \App\Models\PengajuanSurat::where('mahasiswa_id', $this->id)->exists(),
            'data tracer study'       => fn () => $this->tracerStudy()->exists(),
        ];

        $ada = collect($cek)->filter(fn ($fn) => $fn())->keys();

        return $ada->isEmpty()
            ? null
            : 'Mahasiswa ' . $this->nama_lengkap . ' tidak dapat dihapus karena memiliki ' . $ada->implode(', ')
                . '. Ubah status mahasiswa (mis. Non-Aktif / Keluar / DO) untuk menonaktifkannya.';
    }

    // =========================================================================
    // STATUS KRS PER SEMESTER
    // =========================================================================

    public function krsPersetujuans(): HasMany
    {
        return $this->hasMany(KrsPersetujuan::class);
    }

    /**
     * Status KRS mahasiswa pada tahun akademik tertentu.
     */
    public function statusKrsPada(int $tahunAkademikId): string
    {
        // Pakai relasi yang sudah di-eager-load (daftar mahasiswa) agar tidak satu query per baris
        if ($this->relationLoaded('krsPersetujuans')) {
            return optional($this->krsPersetujuans->firstWhere('tahun_akademik_id', $tahunAkademikId))->status
                ?? KrsPersetujuan::BELUM;
        }

        return $this->krsPersetujuans()->where('tahun_akademik_id', $tahunAkademikId)->value('status')
            ?? KrsPersetujuan::BELUM;
    }

    public function krsDisetujuiPada(int $tahunAkademikId): bool
    {
        return $this->statusKrsPada($tahunAkademikId) === KrsPersetujuan::DISETUJUI;
    }

    public function persetujuanKrsPada(int $tahunAkademikId): ?KrsPersetujuan
    {
        return $this->krsPersetujuans()->where('tahun_akademik_id', $tahunAkademikId)->first();
    }

    /**
     * Menyimpan status KRS untuk satu tahun akademik. Bila tahun akademik itu sedang aktif,
     * salinan di kolom mahasiswas.status_krs / catatan_kaprodi ikut diperbarui.
     */
    public function setStatusKrs(int $tahunAkademikId, string $status, ?string $catatan = null, ?int $olehUserId = null): KrsPersetujuan
    {
        $isValidasi = in_array($status, [KrsPersetujuan::DISETUJUI, KrsPersetujuan::DITOLAK], true);

        $persetujuan = KrsPersetujuan::updateOrCreate(
            ['mahasiswa_id' => $this->id, 'tahun_akademik_id' => $tahunAkademikId],
            [
                'status'          => $status,
                'catatan'         => $catatan,
                'divalidasi_oleh' => $isValidasi ? $olehUserId : null,
                'divalidasi_pada' => $isValidasi ? now() : null,
            ]
        );

        $this->unsetRelation('krsPersetujuans');
        $this->statusEvaluasiCache = null;

        $aktifId = TahunAkademik::where('is_active', true)->value('id');
        if ((int) $aktifId === $tahunAkademikId) {
            $this->forceFill(['status_krs' => $status, 'catatan_kaprodi' => $catatan])->save();
        }

        return $persetujuan;
    }
}
