<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nidn',
        'nama_lengkap',
        'jenis_pengajar',
        'is_keuangan',
        'program_studi_id', 
        // --- Identitas Pribadi (Feeder) ---
        'nik', 
        'nuptk', 
        'npwp', 
        'tempat_lahir', 
        'tanggal_lahir', 
        'jenis_kelamin', 
        'nomor_telepon', 
        'alamat',
        // --- Kepegawaian ---
        'status_kepegawaian', 
        'no_sk_pengangkatan', 
        'tmt_sk_pengangkatan', 
        'pangkat_golongan',
        // --- Akademik ---
        'jabatan_akademik',
        'bidang_keahlian',
        'deskripsi_diri',
        'email_institusi',
        'link_google_scholar',
        'link_sinta',
        'foto_profil',
    ];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute()
    {
        if ($this->foto_profil) {
            return asset('storage/' . $this->foto_profil);
        }
        return asset('images/logo.png');
    }

    public function mataKuliahs()
    {
        return $this->hasMany(MataKuliah::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mahasiswaWali()
    {
        return $this->hasMany(Mahasiswa::class, 'dosen_wali_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    public function suratKeputusans()
    {
        return $this->belongsToMany(SuratKeputusan::class, 'dosen_surat_keputusan')
                    ->withPivot('jabatan_dalam_surat')
                    ->withTimestamps();
    }

    public function tridharmas()
    {
        return $this->hasMany(DosenTridharma::class, 'dosen_id')->orderBy('tahun', 'desc')->orderBy('created_at', 'desc');
    }

    public function penelitians()
    {
        return $this->hasMany(DosenTridharma::class, 'dosen_id')->where('unsur', 'penelitian')->orderBy('tahun', 'desc');
    }

    public function pengabdians()
    {
        return $this->hasMany(DosenTridharma::class, 'dosen_id')->where('unsur', 'pengabdian')->orderBy('tahun', 'desc');
    }

    public function penunjangs()
    {
        return $this->hasMany(DosenTridharma::class, 'dosen_id')->where('unsur', 'penunjang')->orderBy('tahun', 'desc');
    }

    /**
     * Menghitung Ringkasan Beban Kerja Dosen (BKD 12-16 SKS) & Angka Kredit (KUM) Standar SISTER Nasional.
     */
    public function hitungRingkasanBkd(): array
    {
        $pivotMkIds = DB::table('dosen_mata_kuliah')
            ->where('dosen_id', $this->id)
            ->pluck('mata_kuliah_id')
            ->toArray();

        $sksPengajaran = (float) MataKuliah::where(function ($q) use ($pivotMkIds) {
                $q->where('dosen_id', $this->id)
                  ->orWhereIn('id', $pivotMkIds);
            })
            ->whereHas('kurikulum', fn($k) => $k->where('is_active', true))
            ->sum('sks');

        // Bimbingan Perwalian Akademik dihitung 1 SKS BKD jika membimbing >= 5 mahasiswa
        $jmlWali = $this->mahasiswaWali()->where('status_mahasiswa', 'Aktif')->count();
        $sksPerwalian = $jmlWali > 0 ? min(2.0, round($jmlWali * 0.1, 2)) : 0.0;

        $sksPendidikan = round($sksPengajaran + $sksPerwalian, 2);

        $tahunBerjalan = (int) date('Y');
        $sksPenelitian = (float) $this->tridharmas()
            ->where('unsur', 'penelitian')
            ->where('tahun', '>=', $tahunBerjalan - 1)
            ->sum('sks_bkd');

        $sksPengabdian = (float) $this->tridharmas()
            ->where('unsur', 'pengabdian')
            ->where('tahun', '>=', $tahunBerjalan - 1)
            ->sum('sks_bkd');

        $sksPenunjangInput = (float) $this->tridharmas()
            ->where('unsur', 'penunjang')
            ->where('tahun', '>=', $tahunBerjalan - 1)
            ->sum('sks_bkd');

        // SK Kepanitiaan otomatis dari E-Office bernilai 0.5 SKS per SK aktif
        $jmlSkSelesai = $this->suratKeputusans()->where('status', 'Selesai')->count();
        $sksPenunjang = round($sksPenunjangInput + min(3.0, $jmlSkSelesai * 0.5), 2);

        $totalSksBkd = round($sksPendidikan + $sksPenelitian + $sksPengabdian + $sksPenunjang, 2);
        $totalKum = round((float) $this->tridharmas()->sum('angka_kredit') + ($sksPendidikan * 1.5) + ($jmlSkSelesai * 1.0), 2);

        $statusBkd = 'MEMENUHI SYARAT BKD (12 - 16 SKS)';
        $warnaBkd = 'success';
        if ($totalSksBkd < 12) {
            $statusBkd = 'KURANG BEBAN (< 12 SKS)';
            $warnaBkd = 'warning';
        } elseif ($totalSksBkd > 16) {
            $statusBkd = 'BEBAN LEBIH / OVERLOAD (> 16 SKS)';
            $warnaBkd = 'primary';
        }

        return [
            'sks_pendidikan' => $sksPendidikan,
            'sks_penelitian' => round($sksPenelitian, 2),
            'sks_pengabdian' => round($sksPengabdian, 2),
            'sks_penunjang'  => $sksPenunjang,
            'total_sks_bkd'  => $totalSksBkd,
            'total_kum'      => $totalKum,
            'status_bkd'     => $statusBkd,
            'warna_bkd'      => $warnaBkd,
        ];
    }

    /**
     * Alasan data dosen tidak boleh dihapus (masih terikat data akademik), atau null bila aman.
     */
    public function alasanTidakBisaDihapus(): ?string
    {
        $cek = [
            'mata kuliah yang diampu'   => fn () => $this->mataKuliahs()->exists() || DB::table('dosen_mata_kuliah')->where('dosen_id', $this->id)->exists(),
            'kelas Verum'               => fn () => \App\Models\VerumKelas::where('dosen_id', $this->id)->exists(),
            'hasil evaluasi (EDOM)'     => fn () => \App\Models\EvaluasiJawaban::where('dosen_id', $this->id)->exists(),
            'bimbingan TA/PPL'          => fn () => \App\Models\BimbinganTugasAkhir::where('pembimbing_1_id', $this->id)->orWhere('pembimbing_2_id', $this->id)->exists(),
            'data tridharma'            => fn () => $this->tridharmas()->exists(),
            'Surat Keputusan'           => fn () => $this->suratKeputusans()->exists(),
            'jabatan Kaprodi'           => fn () => \App\Models\ProgramStudi::where('kaprodi_dosen_id', $this->id)->exists(),
        ];

        $ada = collect($cek)->filter(fn ($fn) => $fn())->keys();

        return $ada->isEmpty()
            ? null
            : 'Dosen ' . $this->nama_lengkap . ' tidak dapat dihapus karena masih memiliki ' . $ada->implode(', ')
                . '. Lepaskan keterkaitan tersebut atau ubah status kepegawaiannya.';
    }
}
