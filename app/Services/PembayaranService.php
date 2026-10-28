<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Notifications\GeneralNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Logika modul Keuangan: filter daftar tagihan, pembuatan tagihan (satuan & massal), dan perubahan status.
 */
class PembayaranService
{
    public const FILTER = ['tipe_user', 'q', 'status', 'semester'];

    /** Query tagihan dengan filter yang sama untuk halaman daftar dan laporan PDF. */
    public function query(array $filter): Builder
    {
        $query = Pembayaran::with(['mahasiswa', 'user']);

        if (($filter['tipe_user'] ?? null) === 'mahasiswa') {
            $query->whereNotNull('mahasiswa_id');
        } elseif (($filter['tipe_user'] ?? null) === 'camaba') {
            $query->whereNull('mahasiswa_id');
        }

        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('mahasiswa', fn ($m) => $m->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nim', 'like', "%{$q}%"))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        if (!empty($filter['status'])) {
            $filter['status'] === 'belum_lunas'
                ? $query->whereIn('status', ['belum_lunas', 'belum_bayar'])
                : $query->where('status', $filter['status']);
        }

        if (!empty($filter['semester'])) {
            $sem = $filter['semester'];
            $query->where(function ($sub) use ($sem) {
                $sub->where('semester', 'like', "%{$sem}%")
                    ->orWhere('keterangan', 'like', "%{$sem}%")
                    ->orWhere('jenis_pembayaran', 'like', "%{$sem}%");
            });
        }

        return $query;
    }

    public function sudahAda(int $mahasiswaId, string $semester, string $jenis): bool
    {
        return Pembayaran::where('mahasiswa_id', $mahasiswaId)
            // Label periode dibandingkan tanpa beda huruf/spasi agar "GANJIL 2026/2027" tidak dobel dengan "Ganjil 2026/2027"
            ->whereRaw('LOWER(TRIM(semester)) = ?', [mb_strtolower(trim($semester))])
            ->where('jenis_pembayaran', $jenis)
            ->exists();
    }

    /** @return int jumlah tagihan baru yang dibuat */
    public function generateMassal(iterable $mahasiswas, string $semester, string $jenis, int $jumlah): int
    {
        $keterangan = ucwords(str_replace('_', ' ', $jenis)) . ' - ' . $semester;

        return DB::transaction(function () use ($mahasiswas, $semester, $jenis, $jumlah, $keterangan) {
            $count = 0;
            foreach ($mahasiswas as $mhs) {
                if (!$mhs->user_id || $this->sudahAda($mhs->id, $semester, $jenis)) {
                    continue;
                }
                Pembayaran::create([
                    'user_id'          => $mhs->user_id,
                    'mahasiswa_id'     => $mhs->id,
                    'semester'         => $semester,
                    'jenis_pembayaran' => $jenis,
                    'jumlah'           => $jumlah,
                    'status'           => 'belum_lunas',
                    'keterangan'       => $keterangan,
                ]);
                $count++;
            }
            return $count;
        });
    }

    public function tandaiLunas(Pembayaran $pembayaran): void
    {
        $pembayaran->update([
            'status'            => 'lunas',
            'tanggal_bayar'     => $pembayaran->tanggal_bayar ?? now(),
            'diverifikasi_oleh' => Auth::id(),
            'diverifikasi_pada' => now(),
        ]);

        $this->beritahu(
            $pembayaran,
            'Pembayaran Diverifikasi Lunas',
            'Tagihan ' . strtoupper(str_replace('_', ' ', $pembayaran->jenis_pembayaran ?? 'Kuliah')) . ' (' . $pembayaran->semester . ') telah diverifikasi LUNAS oleh Bagian Keuangan.',
            $pembayaran->mahasiswa_id ? route('pembayaran.riwayat') : route('dashboard'),
            'bi-check-circle-fill text-success'
        );
    }

    public function beriDispensasi(Pembayaran $pembayaran): void
    {
        $catatan = $pembayaran->keterangan;
        if (!str_contains(strtolower((string) $catatan), 'dispensasi')) {
            $catatan = trim(($catatan ? $catatan . ' | ' : '') . 'Dispensasi Cicilan KRS');
        }

        $pembayaran->update([
            'status'            => 'dispensasi',
            'keterangan'        => $catatan,
            'diverifikasi_oleh' => Auth::id(),
            'diverifikasi_pada' => now(),
        ]);

        $this->beritahu(
            $pembayaran,
            'Dispensasi Pembayaran KRS Aktif',
            'Tagihan semester ' . $pembayaran->semester . ' telah diberikan status DISPENSASI CICILAN. Anda kini dapat mengakses pengisian KRS.',
            route('krs.index'),
            'bi-unlock-fill text-primary'
        );
    }

    public function tolakBukti(Pembayaran $pembayaran, string $alasan): void
    {
        $pembayaran->update([
            'status'        => 'belum_lunas',
            'tanggal_bayar' => null,
            'keterangan'    => trim(($pembayaran->keterangan ? $pembayaran->keterangan . ' | ' : '') . 'Bukti ditolak: ' . $alasan),
        ]);

        $this->beritahu(
            $pembayaran,
            'Bukti Pembayaran Ditolak',
            'Bukti pembayaran tagihan ' . $pembayaran->semester . ' ditolak Bagian Keuangan. Alasan: ' . $alasan . '. Silakan unggah ulang bukti yang valid.',
            $pembayaran->mahasiswa_id ? route('pembayaran.riwayat') : route('pmb.pembayaran.show'),
            'bi-x-circle-fill text-danger'
        );
    }

    private function beritahu(Pembayaran $pembayaran, string $judul, string $pesan, string $url, string $ikon): void
    {
        $target = $pembayaran->user ?? optional($pembayaran->mahasiswa)->user;
        if ($target) {
            $target->notify(new GeneralNotification($judul, $pesan, $url, $ikon));
        }
    }
}
