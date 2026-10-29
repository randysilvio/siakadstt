<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dosen extends Model
{
    use \App\Models\Concerns\TercatatAktivitas;

    public function labelAktivitas(): string
    {
        return 'data dosen ' . $this->nama_lengkap;
    }

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
    /** Dosen tetap = status kepegawaian "Dosen Tetap" (atau, bila kosong, jenis pengajar "Dosen Tetap"). */
    public function scopeTetap($query)
    {
        $tetap = fn ($q, string $kolom) => $q->whereRaw("LOWER($kolom) LIKE ?", ['%tetap%'])->whereRaw("LOWER($kolom) NOT LIKE ?", ['%tidak%']);

        return $query->where(fn ($q) => $tetap($q, 'dosens.status_kepegawaian')
            ->orWhere(fn ($q2) => $tetap($q2->where(fn ($k) => $k->whereNull('dosens.status_kepegawaian')->orWhere('dosens.status_kepegawaian', '')), 'dosens.jenis_pengajar')));
    }

    public function hitungRingkasanBkd(): array
    {
        // BKD adalah beban PER SEMESTER: hanya mata kuliah yang ditawarkan pada semester aktif (ganjil/genap)
        // dan kegiatan Tridharma pada semester tersebut yang dihitung.
        $ta = TahunAkademik::where('is_active', true)->first();
        $semesterMk = $ta ? app(\App\Services\AcademicRuleService::class)->semesterDitawarkan($ta) : null;

        $pivotMkIds = DB::table('dosen_mata_kuliah')
            ->where('dosen_id', $this->id)
            ->pluck('mata_kuliah_id')
            ->toArray();

        // Pengajaran: setiap dosen pengampu (dosen utama maupun anggota team teaching) mendapat SKS penuh,
        // dikalikan jumlah kelas paralel (rombel) mata kuliah tersebut pada jadwal.
        $mkAmpu = MataKuliah::where(function ($q) use ($pivotMkIds) {
                $q->where('dosen_id', $this->id)
                  ->orWhereIn('id', $pivotMkIds);
            })
            ->whereHas('kurikulum', fn($k) => $k->where('is_active', true))
            ->when($semesterMk, fn ($q) => $q->whereIn('semester', $semesterMk))
            ->with('jadwals:id,mata_kuliah_id,nama_kelas')
            ->get(['id', 'sks', 'semester']);

        $jumlahKelasAjar = 0;
        $sksPengajaran = 0.0;
        foreach ($mkAmpu as $mk) {
            $kelas = max(1, $mk->jadwals->map(fn ($j) => strtoupper(trim((string) ($j->nama_kelas ?: 'A'))))->unique()->count());
            $jumlahKelasAjar += $kelas;
            $sksPengajaran += (float) $mk->sks * $kelas;
        }

        // Bimbingan Perwalian Akademik: 0,1 SKS per mahasiswa wali aktif, maksimal 2 SKS
        $jmlWali = $this->mahasiswaWali()->where('status_mahasiswa', 'Aktif')->count();
        $sksPerwalian = $jmlWali > 0 ? min(2.0, round($jmlWali * 0.1, 2)) : 0.0;

        $sksPendidikan = round($sksPengajaran + $sksPerwalian, 2);

        $kegiatanSemesterIni = function (string $unsur) use ($ta) {
            $q = $this->tridharmas()->where('unsur', $unsur);
            if ($ta) {
                $tahun = array_map('intval', explode('/', (string) $ta->tahun));
                $semester = in_array($ta->semester, ['Ganjil', 'Gasal'], true) ? 'Ganjil' : 'Genap';
                $q->where('semester', $semester)->whereIn('tahun', array_filter($tahun));
            } else {
                $q->where('tahun', '>=', (int) date('Y') - 1);
            }
            return (float) $q->sum('sks_bkd');
        };

        $sksPenelitian = $kegiatanSemesterIni('penelitian');
        $sksPengabdian = $kegiatanSemesterIni('pengabdian');
        $sksPenunjangInput = $kegiatanSemesterIni('penunjang');

        // SK Kepanitiaan otomatis dari E-Office bernilai 0.5 SKS per SK aktif
        // Hanya SK yang terbit pada semester aktif (Ganjil: Agustus–Januari, Genap: Februari–Juli)
        $jmlSkSelesai = $this->suratKeputusans()->where('status', 'Selesai')
            ->when($ta, function ($q) use ($ta) {
                $tahun = array_map('intval', explode('/', (string) $ta->tahun));
                $awal = $tahun[0] ?? (int) date('Y');
                $akhir = $tahun[1] ?? $awal + 1;
                [$mulai, $selesai] = in_array($ta->semester, ['Ganjil', 'Gasal'], true)
                    ? ["{$awal}-08-01", "{$akhir}-01-31"]
                    : ["{$akhir}-02-01", "{$akhir}-07-31"];
                $q->where(fn ($w) => $w->whereBetween('surat_keputusans.tanggal_terbit', [$mulai, $selesai])
                    ->orWhere(fn ($w2) => $w2->whereNull('surat_keputusans.tanggal_terbit')->whereBetween('surat_keputusans.created_at', [$mulai . ' 00:00:00', $selesai . ' 23:59:59'])));
            })
            ->count();
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
            'sks_pengajaran'    => round($sksPengajaran, 2),
            'jumlah_mk_ajar'    => $mkAmpu->count(),
            'jumlah_kelas_ajar' => $jumlahKelasAjar,
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
