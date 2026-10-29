<?php

namespace App\Services;

use App\Models\EvaluasiJawaban;
use App\Models\EvaluasiSesi;
use App\Models\KegiatanAkademik;
use App\Models\LogAktivitas;
use App\Models\PmbPeriod;
use App\Models\TahunAkademik;
use Carbon\Carbon;

/**
 * Kalender Akademik sebagai panel kendali. Acara berjenis selain "informasi" mengatur perilaku sistem:
 *
 *  - krs          → tanggal pengisian KRS Tahun Akademik (dibaca CekPeriodeKrsMiddleware)
 *  - edom         → Sesi Evaluasi Dosen (aktif & rentang tanggal)
 *  - pmb          → tanggal buka/tutup Gelombang PMB
 *  - validasi_krs → Dosen Wali/Kaprodi hanya dapat memvalidasi KRS di rentang ini (Admin selalu bisa)
 *  - input_nilai  → Dosen hanya dapat menyimpan nilai di rentang ini (Admin selalu bisa)
 *
 * Tanggal selesai ikut dihitung (berlaku sampai 23.59 WIT). Saklar manual "Status KRS = tutup" di Pengaturan
 * tetap menang atas kalender (tutup darurat). Bila suatu jenis tidak dijadwalkan, aturan lama berlaku.
 */
class KalenderPengendali
{
    public const JENIS = [
        'informasi'    => 'Informasi (tanpa efek sistem)',
        'krs'          => 'Periode pengisian KRS',
        'validasi_krs' => 'Periode validasi KRS (Dosen Wali / Kaprodi)',
        'edom'         => 'Periode evaluasi dosen (EDOM)',
        'input_nilai'  => 'Periode input nilai dosen',
        'pmb'          => 'Gelombang PMB (buka–tutup pendaftaran)',
    ];

    /** Jenis yang terikat pada satu Tahun Akademik. */
    public const PER_TAHUN_AKADEMIK = ['krs', 'validasi_krs', 'edom', 'input_nilai'];

    public static function pengendali(KegiatanAkademik $k): bool
    {
        return ($k->jenis ?? 'informasi') !== 'informasi';
    }

    /** Status acara hari ini: akan_datang | berlangsung | selesai. */
    public static function status(KegiatanAkademik $k): string
    {
        $hari = now()->startOfDay();
        return match (true) {
            $hari->lt(Carbon::parse($k->tanggal_mulai)->startOfDay()) => 'akan_datang',
            $hari->gt(Carbon::parse($k->tanggal_selesai)->startOfDay()) => 'selesai',
            default => 'berlangsung',
        };
    }

    public static function acara(string $jenis, ?int $tahunAkademikId = null, ?int $pmbPeriodId = null): ?KegiatanAkademik
    {
        return KegiatanAkademik::where('jenis', $jenis)
            ->when($tahunAkademikId, fn ($q) => $q->where('tahun_akademik_id', $tahunAkademikId))
            ->when($pmbPeriodId, fn ($q) => $q->where('pmb_period_id', $pmbPeriodId))
            ->first();
    }

    /**
     * Pesan bila aksi dibatasi kalender (null = boleh). Dipakai untuk validasi KRS & input nilai.
     */
    public static function alasanDiluarPeriode(string $jenis, ?TahunAkademik $ta): ?string
    {
        if (!$ta) {
            return null;
        }
        $k = self::acara($jenis, $ta->id);
        if (!$k) {
            return null;
        }
        $nama = $jenis === 'validasi_krs' ? 'Periode validasi KRS' : 'Periode input nilai';
        $rentang = Carbon::parse($k->tanggal_mulai)->translatedFormat('d F Y') . ' – ' . Carbon::parse($k->tanggal_selesai)->translatedFormat('d F Y');
        return match (self::status($k)) {
            'akan_datang' => "{$nama} {$ta->nama_semester} belum dibuka ({$rentang}).",
            'selesai'     => "{$nama} {$ta->nama_semester} sudah ditutup ({$rentang}). Hubungi Admin bila perlu perpanjangan.",
            default       => null,
        };
    }

    /**
     * Validasi tambahan untuk acara pengendali (tidak bentrok, tautan wajib). @return string|null pesan galat
     */
    public static function periksaBentrok(array $data, ?KegiatanAkademik $kecuali = null): ?string
    {
        $jenis = $data['jenis'] ?? 'informasi';
        if ($jenis === 'informasi') {
            return null;
        }
        if (in_array($jenis, self::PER_TAHUN_AKADEMIK, true) && empty($data['tahun_akademik_id'])) {
            return 'Pilih Tahun Akademik yang dikendalikan acara ini.';
        }
        if ($jenis === 'pmb' && empty($data['pmb_period_id'])) {
            return 'Pilih Gelombang PMB yang dikendalikan acara ini.';
        }
        $ada = KegiatanAkademik::where('jenis', $jenis)
            ->when($jenis === 'pmb', fn ($q) => $q->where('pmb_period_id', $data['pmb_period_id']), fn ($q) => $q->where('tahun_akademik_id', $data['tahun_akademik_id']))
            ->when($kecuali, fn ($q) => $q->where('id', '!=', $kecuali->id))
            ->first();
        return $ada ? '"' . (self::JENIS[$jenis] ?? $jenis) . '" untuk periode ini sudah dijadwalkan pada acara "' . $ada->judul_kegiatan . '". Ubah acara tersebut saja.' : null;
    }

    /** Terapkan tanggal acara pengendali ke data yang dikendalikannya. */
    public static function terapkan(KegiatanAkademik $k): void
    {
        LogAktivitas::tanpaCatat(function () use ($k) {
            $mulai = Carbon::parse($k->tanggal_mulai)->toDateString();
            $selesai = Carbon::parse($k->tanggal_selesai)->toDateString();

            switch ($k->jenis) {
                case 'krs':
                    TahunAkademik::whereKey($k->tahun_akademik_id)->update(['tanggal_mulai_krs' => $mulai, 'tanggal_selesai_krs' => $selesai]);
                    break;
                case 'pmb':
                    PmbPeriod::whereKey($k->pmb_period_id)->update(['tanggal_buka' => $mulai, 'tanggal_tutup' => $selesai]);
                    break;
                case 'edom':
                    $sesi = $k->evaluasi_sesi_id ? EvaluasiSesi::find($k->evaluasi_sesi_id) : null;
                    $isi = ['nama_sesi' => $k->judul_kegiatan, 'tahun_akademik_id' => $k->tahun_akademik_id, 'tanggal_mulai' => $mulai, 'tanggal_selesai' => $selesai, 'is_active' => true];
                    if ($sesi) {
                        $sesi->update($isi);
                    } else {
                        $sesi = EvaluasiSesi::create($isi);
                        $k->forceFill(['evaluasi_sesi_id' => $sesi->id])->saveQuietly();
                    }
                    break;
            }
        });
    }

    /** Dipanggil saat acara dihapus / berubah jenis: lepaskan kendali tanpa menghapus arsip. */
    public static function lepaskan(KegiatanAkademik $k): void
    {
        if ($k->jenis === 'edom' && $k->evaluasi_sesi_id && ($sesi = EvaluasiSesi::find($k->evaluasi_sesi_id))) {
            LogAktivitas::tanpaCatat(function () use ($sesi) {
                EvaluasiJawaban::where('evaluasi_sesi_id', $sesi->id)->exists() ? $sesi->update(['is_active' => false]) : $sesi->delete();
            });
        }
    }

    // ------------------------------------------------------------------ penanda untuk form lama

    public static function pengendaliKrs(?TahunAkademik $ta): ?KegiatanAkademik
    {
        return $ta ? self::acara('krs', $ta->id) : null;
    }

    public static function pengendaliPmb(?PmbPeriod $p): ?KegiatanAkademik
    {
        return $p ? self::acara('pmb', null, $p->id) : null;
    }

    public static function pengendaliEdom(?EvaluasiSesi $s): ?KegiatanAkademik
    {
        return $s ? KegiatanAkademik::where('jenis', 'edom')->where('evaluasi_sesi_id', $s->id)->first() : null;
    }
}
