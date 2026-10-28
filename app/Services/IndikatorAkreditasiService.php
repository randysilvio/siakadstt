<?php

namespace App\Services;

use App\Models\BimbinganTugasAkhir;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\TracerStudy;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Indikator kuantitatif akreditasi program studi sesuai matriks penilaian BAN-PT (IAPS 4.0, PerBAN-PT 5/2019,
 * Lampiran 6a Program Sarjana) dan LAMDIK (PerLAMDIK 3/2025 Buku 4). Kedua instrumen memakai rumus yang sama
 * untuk butir-butir di bawah. Program studi Teologi & PAK masuk rumpun Sosial Humaniora.
 *
 * Skor 0–4 (4 = terbaik) dihitung persis dari rumus matriks penilaian.
 */
class IndikatorAkreditasiService
{
    /** Status kepegawaian / jenis pengajar yang dihitung sebagai dosen tetap. */
    public const STATUS_TETAP = ['Dosen Tetap'];

    public function dosenTetapQuery()
    {
        return Dosen::tetap();
    }

    // ------------------------------------------------------------------ rumus skor (rumpun Sosial Humaniora)

    /** Butir RMD: rasio mahasiswa terhadap DTPS. Skor 4 bila 25 ≤ RMD ≤ 35. */
    public static function skorRasio(?float $rmd): ?float
    {
        if ($rmd === null) {
            return null;
        }
        return round(match (true) {
            $rmd < 25   => 4 * $rmd / 25,
            $rmd <= 35  => 4,
            $rmd <= 50  => (200 - 4 * $rmd) / 15,
            default     => 0,
        }, 2);
    }

    /** Butir NDTPS: jumlah DTPS. Skor 4 bila ≥ 12; 0 bila < 3. */
    public static function skorJumlahDtps(int $n): float
    {
        return round($n >= 12 ? 4 : ($n >= 3 ? ((2 * $n) + 12) / 9 : 0), 2);
    }

    /** Butir PDTT: persentase dosen tidak tetap. Skor 4 bila ≤ 10%; 0 bila > 40%. */
    public static function skorDosenTidakTetap(?float $persen): ?float
    {
        if ($persen === null) {
            return null;
        }
        $p = $persen / 100;
        return round($p <= 0.10 ? 4 : ($p <= 0.40 ? (14 - 20 * $p) / 3 : 0), 2);
    }

    /** Butir RDPU: rata-rata bimbingan TA per pembimbing utama. Skor 4 bila ≤ 6; 0 bila > 10. */
    public static function skorBimbingan(?float $rdpu): ?float
    {
        if ($rdpu === null) {
            return null;
        }
        return round($rdpu <= 6 ? 4 : ($rdpu <= 10 ? 7 - $rdpu / 2 : 0), 2);
    }

    /** Butir RIPK: rata-rata IPK lulusan 3 tahun. Skor 4 bila ≥ 3,25. */
    public static function skorIpkLulusan(?float $ripk): ?float
    {
        if ($ripk === null) {
            return null;
        }
        return round($ripk >= 3.25 ? 4 : ($ripk >= 2.00 ? ((8 * $ripk) - 6) / 5 : 0), 2);
    }

    /** Butir PTW: persentase kelulusan tepat waktu. Skor 4 bila ≥ 50%; di bawahnya 1 + 6 × PTW. */
    public static function skorTepatWaktu(?float $persen): ?float
    {
        if ($persen === null) {
            return null;
        }
        $p = $persen / 100;
        return round($p >= 0.50 ? 4 : 1 + 6 * $p, 2);
    }

    /** Butir WT: rata-rata waktu tunggu (bulan). Skor 4 bila < 6; 0 bila > 18. */
    public static function skorWaktuTunggu(?float $bulan): ?float
    {
        if ($bulan === null) {
            return null;
        }
        return round($bulan < 6 ? 4 : ($bulan <= 18 ? (18 - $bulan) / 3 : 0), 2);
    }

    /** Butir PBS: kesesuaian bidang kerja. Skor 4 bila ≥ 60%. */
    public static function skorKesesuaian(?float $persen): ?float
    {
        if ($persen === null) {
            return null;
        }
        $p = $persen / 100;
        return round($p >= 0.60 ? 4 : 20 * $p / 3, 2);
    }

    /** Batas minimal responden tracer (Prmin): 30% bila lulusan 3 tahun ≥ 300, selain itu 50% − (NL/300 × 20%). */
    public static function respondenMinimal(int $jumlahLulusan): float
    {
        return $jumlahLulusan >= 300 ? 30.0 : round(50 - ($jumlahLulusan / 300) * 20, 1);
    }

    // ------------------------------------------------------------------ data

    /**
     * Indikator dosen per program studi.
     * DTPS = dosen tetap ber-homebase prodi tersebut. Dosen tidak tetap = dosen non-tetap yang ber-homebase
     * atau mengampu mata kuliah kurikulum aktif prodi tersebut.
     */
    public function perProdi(): Collection
    {
        $tetapIds = $this->dosenTetapQuery()->pluck('id');

        return ProgramStudi::orderBy('nama_prodi')->get()->map(function (ProgramStudi $prodi) use ($tetapIds) {
            $nm = Mahasiswa::where('program_studi_id', $prodi->id)->where('status_mahasiswa', 'Aktif')->count();

            $dtps = Dosen::where('program_studi_id', $prodi->id)->whereIn('id', $tetapIds)->pluck('id');

            $mkProdi = MataKuliah::whereHas('kurikulum', fn ($k) => $k->where('is_active', true)->where('program_studi_id', $prodi->id));
            $pengampu = (clone $mkProdi)->whereNotNull('dosen_id')->pluck('dosen_id')
                ->merge(\DB::table('dosen_mata_kuliah')->whereIn('mata_kuliah_id', (clone $mkProdi)->select('id'))->pluck('dosen_id'));
            $dtt = Dosen::whereNotIn('id', $tetapIds)
                ->where(fn ($q) => $q->where('program_studi_id', $prodi->id)->orWhereIn('id', $pengampu->unique()))
                ->count();

            $rmd = $dtps->count() > 0 ? round($nm / $dtps->count(), 1) : null;
            $pdtt = ($dtps->count() + $dtt) > 0 ? round($dtt / ($dtps->count() + $dtt) * 100, 1) : null;

            // Bimbingan TA aktif (belum selesai) mahasiswa prodi ini per pembimbing utama
            $bimbingan = BimbinganTugasAkhir::whereNotNull('pembimbing_1_id')
                ->where('status', '!=', 'Selesai / Lulus')
                ->whereHas('mahasiswa', fn ($m) => $m->where('program_studi_id', $prodi->id))
                ->get(['pembimbing_1_id']);
            $rdpu = $bimbingan->isNotEmpty() ? round($bimbingan->count() / $bimbingan->pluck('pembimbing_1_id')->unique()->count(), 1) : null;

            return (object) [
                'prodi'          => $prodi,
                'mahasiswa'      => $nm,
                'dtps'           => $dtps->count(),
                'dtt'            => $dtt,
                'rasio'          => $rmd,
                'rasio_teks'     => $rmd !== null ? '1 : ' . rtrim(rtrim(number_format($rmd, 1, ',', ''), '0'), ',') : 'N/A',
                'skor_rasio'     => self::skorRasio($rmd),
                'skor_dtps'      => self::skorJumlahDtps($dtps->count()),
                'persen_dtt'     => $pdtt,
                'skor_dtt'       => self::skorDosenTidakTetap($pdtt),
                'rdpu'           => $rdpu,
                'skor_bimbingan' => self::skorBimbingan($rdpu),
            ];
        });
    }

    /** Rasio dosen tetap : mahasiswa aktif tingkat institusi (untuk kartu ringkas). */
    public function rasioInstitusi(): array
    {
        $nm = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $ndt = $this->dosenTetapQuery()->count();
        $rmd = $ndt > 0 ? round($nm / $ndt, 1) : null;

        return [
            'mahasiswa'   => $nm,
            'dosen_tetap' => $ndt,
            'rasio'       => $rmd,
            'teks'        => $rmd !== null ? '1 : ' . rtrim(rtrim(number_format($rmd, 1, ',', ''), '0'), ',') : 'N/A',
            'skor'        => self::skorRasio($rmd),
        ];
    }

    /**
     * Profil lulusan 3 tahun terakhir: rata-rata IPK, masa studi, dan % tepat waktu (≤ 4 tahun untuk sarjana).
     * Masa studi dihitung dari 1 September tahun masuk sampai tanggal lulus.
     */
    public function lulusan(int $tahun = 3): array
    {
        $dari = now()->subYears($tahun)->startOfYear();
        $lulusan = Mahasiswa::isiRekapNilai(
            Mahasiswa::where('status_mahasiswa', 'Lulus')->whereNotNull('tanggal_lulus')->whereDate('tanggal_lulus', '>=', $dari)->get()
        );

        $baris = $lulusan->map(function ($m) {
            $masuk = Carbon::create((int) $m->tahun_masuk, 9, 1);
            $masa = round($masuk->diffInDays(Carbon::parse($m->tanggal_lulus)) / 365.25, 2);
            return ['ipk' => $m->hitungIpk(), 'masa' => $masa, 'tepat' => $masa <= 4.0];
        });

        $n = $baris->count();
        $ripk = $n ? round($baris->avg('ipk'), 2) : null;
        $ptw = $n ? round($baris->where('tepat', true)->count() / $n * 100, 1) : null;

        return [
            'jumlah'      => $n,
            'rata_ipk'    => $ripk,
            'skor_ipk'    => self::skorIpkLulusan($ripk),
            'masa_studi'  => $n ? round($baris->avg('masa'), 1) : null,
            'tepat_waktu' => $ptw,
            'skor_tepat'  => self::skorTepatWaktu($ptw),
        ];
    }

    /** Indikator tracer study 3 tahun terakhir beserta kecukupan jumlah responden. */
    public function tracer(int $tahun = 3): array
    {
        $tahunMin = (int) now()->year - $tahun;
        $tracers = TracerStudy::where('tahun_lulus', '>=', $tahunMin)->get();
        $nl = Mahasiswa::where('status_mahasiswa', 'Lulus')->whereNotNull('tanggal_lulus')->whereYear('tanggal_lulus', '>=', $tahunMin)->count();

        $n = $tracers->count();
        $wt = $n ? round($tracers->avg('waktu_tunggu_bulan'), 1) : null;
        $pbs = $n ? round($tracers->whereIn('kesesuaian_bidang', ['Sangat Sesuai', 'Sesuai'])->count() / $n * 100, 1) : null;
        $respon = $nl > 0 ? round($n / $nl * 100, 1) : null;
        $prmin = self::respondenMinimal($nl);
        $faktor = ($respon !== null && $respon < $prmin) ? $respon / $prmin : 1;

        $skorWt = self::skorWaktuTunggu($wt);
        $skorPbs = self::skorKesesuaian($pbs);

        return [
            'responden'        => $n,
            'lulusan'          => $nl,
            'persen_responden' => $respon,
            'responden_min'    => $prmin,
            'waktu_tunggu'     => $wt,
            'skor_wt'          => $skorWt !== null ? round($skorWt * $faktor, 2) : null,
            'kesesuaian'       => $pbs,
            'skor_pbs'         => $skorPbs !== null ? round($skorPbs * $faktor, 2) : null,
        ];
    }

    /** Warna lencana skor (seragam di semua halaman). */
    public static function warnaSkor(?float $skor): string
    {
        return match (true) {
            $skor === null => 'secondary',
            $skor >= 3.5   => 'success',
            $skor >= 2     => 'warning',
            default        => 'danger',
        };
    }
}
