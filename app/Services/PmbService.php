<?php

namespace App\Services;

use App\Models\Camaba;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\PmbKepanitiaan;
use App\Models\Role;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Support\BerkasPrivat;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Logika bisnis Penerimaan Mahasiswa Baru (PMB): ruang lingkup data per pengguna (admin / panitia),
 * verifikasi pembayaran formulir, keputusan seleksi, dan konversi camaba menjadi mahasiswa.
 */
class PmbService
{
    // =========================================================================
    // RUANG LINGKUP AKSES
    // =========================================================================

    /** Admin melihat semua tahun PMB; panitia hanya tahun kepanitiaannya yang masih aktif. */
    public function bolehKelolaSemua(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /** @return array<int>|null null = tanpa batas (admin) */
    public function tahunDiizinkan(User $user): ?array
    {
        return $this->bolehKelolaSemua($user) ? null : $user->tahunPanitiaPmbAktif();
    }

    public function queryPendaftar(User $user, array $filter = []): Builder
    {
        $query = Camaba::with(['user', 'prodi1', 'period'])->latest();

        $tahun = $this->tahunDiizinkan($user);
        if ($tahun !== null) {
            $query->tahunPmb($tahun);
        }

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhere('no_pendaftaran', 'like', "%{$search}%");
            });
        }
        if (!empty($filter['status'])) {
            $query->where('status_pendaftaran', $filter['status']);
        }
        if (!empty($filter['pmb_period_id'])) {
            $query->where('pmb_period_id', $filter['pmb_period_id']);
        }
        if (!empty($filter['pilihan_prodi_1_id'])) {
            $query->where('pilihan_prodi_1_id', $filter['pilihan_prodi_1_id']);
        }

        return $query;
    }

    public function bolehAksesCamaba(User $user, Camaba $camaba): bool
    {
        $tahun = $this->tahunDiizinkan($user);
        if ($tahun === null) {
            return true;
        }
        return in_array((int) optional($camaba->period)->tahun_pmb, $tahun, true);
    }

    public function pastikanAkses(User $user, Camaba $camaba): void
    {
        if (!$this->bolehAksesCamaba($user, $camaba)) {
            abort(403, 'Pendaftar ini berada di luar tahun kepanitiaan PMB Anda.');
        }
    }

    public function tagihanFormulir(Camaba $camaba): ?Pembayaran
    {
        return Pembayaran::with('verifikator')
            ->where('user_id', $camaba->user_id)
            ->where('jenis_pembayaran', 'formulir_pmb')
            ->latest()
            ->first();
    }

    public function formulirLunas(Camaba $camaba): bool
    {
        return Pembayaran::where('user_id', $camaba->user_id)
            ->where('jenis_pembayaran', 'formulir_pmb')
            ->where('status', 'lunas')
            ->exists();
    }

    // =========================================================================
    // PROSES ADMISI
    // =========================================================================

    public function verifikasiPembayaran(Pembayaran $pembayaran, User $oleh): void
    {
        if ($pembayaran->jenis_pembayaran !== 'formulir_pmb') {
            throw new DomainException('Hanya tagihan formulir PMB yang diverifikasi dari modul PMB.');
        }

        $pembayaran->update([
            'status'            => 'lunas',
            'tanggal_bayar'     => $pembayaran->tanggal_bayar ?? now(),
            'keterangan'        => 'Diverifikasi oleh Panitia PMB',
            'diverifikasi_oleh' => $oleh->id,
            'diverifikasi_pada' => now(),
        ]);

        optional($pembayaran->user)->notify(new GeneralNotification(
            'Pembayaran Formulir PMB Lunas',
            'Pembayaran formulir pendaftaran Anda telah diverifikasi. Silakan lengkapi biodata dan unggah berkas persyaratan.',
            route('dashboard'),
            'bi-check-circle-fill text-success'
        ));
    }

    /**
     * Meluluskan camaba: membuat data mahasiswa dengan NIM baru dan mengganti peran camaba menjadi mahasiswa.
     *
     * @return string NIM baru
     */
    public function luluskan(Camaba $camaba, User $oleh): string
    {
        if ($camaba->is_migrated || $camaba->status_pendaftaran === 'lulus') {
            throw new DomainException('Camaba ini sudah menjadi mahasiswa aktif.');
        }
        if (!$this->formulirLunas($camaba)) {
            throw new DomainException('Gagal meluluskan: Tagihan formulir pendaftaran Camaba ini belum berstatus LUNAS.');
        }
        if (!$camaba->pilihan_prodi_1_id || !$camaba->tempat_lahir) {
            throw new DomainException('Biodata belum lengkap. Camaba wajib melengkapi biodata terlebih dahulu.');
        }
        if ($camaba->status_pendaftaran === 'tidak_lulus') {
            throw new DomainException('Pendaftar ini sudah dinyatakan TIDAK LULUS. Ubah keputusan seleksi tidak dapat dilakukan dari tombol kelulusan.');
        }
        if ($camaba->status_pendaftaran !== 'menunggu_verifikasi') {
            throw new DomainException('Pendaftar belum menyerahkan biodata untuk diverifikasi.');
        }

        return DB::transaction(function () use ($camaba, $oleh) {
            // Kunci baris prodi sebagai mutex agar dua panitia yang meluluskan bersamaan tidak mendapat NIM yang sama
            DB::table('program_studis')->where('id', $camaba->pilihan_prodi_1_id)->lockForUpdate()->first();

            // Tahun masuk = tahun PMB (angkatan) gelombang, bukan tanggal hari ini
            $tahunMasuk = (string) (optional($camaba->period)->tahun_pmb ?: date('Y'));
            $kodeProdi = optional($camaba->prodi1)->kode_prodi ?: str_pad((string) $camaba->pilihan_prodi_1_id, 2, '0', STR_PAD_LEFT);
            $nimBaru = $this->nimBerikutnya($tahunMasuk . $kodeProdi);

            Mahasiswa::create([
                'user_id'           => $camaba->user_id,
                'nim'               => $nimBaru,
                'nama_lengkap'      => $camaba->user->name,
                'program_studi_id'  => $camaba->pilihan_prodi_1_id,
                'tempat_lahir'      => $camaba->tempat_lahir ?? 'Belum Diisi',
                'tanggal_lahir'     => $camaba->tanggal_lahir ?? now(),
                'jenis_kelamin'     => $camaba->jenis_kelamin ?? 'L',
                'agama'             => $camaba->agama ?? 'Kristen Protestan',
                'nomor_telepon'     => $camaba->no_hp ?? '-',
                'nisn'              => $camaba->nisn,
                'alamat'            => $camaba->alamat ?? 'Belum Diisi',
                // NIK dibiarkan kosong agar terdeteksi wajib dilengkapi sebelum sinkron Feeder
                'nik'               => null,
                'kewarganegaraan'   => 'WNI',
                'nama_ibu_kandung'  => 'Belum Diisi',
                'jalur_pendaftaran' => 'Mandiri (PMB)',
                'tahun_masuk'       => $tahunMasuk,
                'status_mahasiswa'  => 'Aktif',
            ]);

            $camaba->update([
                'status_pendaftaran' => 'lulus',
                'is_migrated'        => true,
                'diputuskan_oleh'    => $oleh->id,
                'diputuskan_pada'    => now(),
            ]);

            $user = $camaba->user;
            $roleCamaba = Role::where('name', 'camaba')->first();
            $roleMhs = Role::firstOrCreate(['name' => 'mahasiswa'], ['display_name' => 'Mahasiswa']);
            if ($roleCamaba) {
                $user->roles()->detach($roleCamaba->id);
            }
            $user->roles()->syncWithoutDetaching([$roleMhs->id]);

            $user->notify(new GeneralNotification(
                'Selamat! Anda Diterima',
                'Anda dinyatakan LULUS seleksi PMB dan terdaftar sebagai mahasiswa dengan NIM ' . $nimBaru . '.',
                route('dashboard'),
                'bi-mortarboard-fill text-success'
            ));

            return $nimBaru;
        });
    }

    public function tolak(Camaba $camaba, ?string $alasan, User $oleh): void
    {
        if ($camaba->is_migrated) {
            throw new DomainException('Camaba yang sudah dimigrasikan menjadi mahasiswa tidak dapat ditolak.');
        }

        $alasan = trim(strip_tags((string) $alasan)) ?: null;

        $camaba->update([
            'status_pendaftaran' => 'tidak_lulus',
            'catatan_seleksi'    => $alasan,
            'diputuskan_oleh'    => $oleh->id,
            'diputuskan_pada'    => now(),
        ]);

        optional($camaba->user)->notify(new GeneralNotification(
            'Hasil Seleksi PMB',
            'Mohon maaf, pendaftaran Anda tidak dapat diterima.' . ($alasan ? ' Keterangan panitia: ' . $alasan : ''),
            route('dashboard'),
            'bi-x-circle-fill text-danger'
        ));
    }

    /** Menghapus pendaftar beserta dokumen, tagihan formulir, dan akunnya. */
    public function hapusPendaftar(Camaba $camaba): void
    {
        if ($camaba->is_migrated) {
            throw new DomainException('Pendaftar yang sudah menjadi mahasiswa tidak dapat dihapus dari modul PMB.');
        }

        DB::transaction(function () use ($camaba) {
            $userId = $camaba->user_id;

            foreach ($camaba->documents as $doc) {
                BerkasPrivat::hapus($doc->path_file);
                $doc->delete();
            }

            Pembayaran::where('user_id', $userId)->where('jenis_pembayaran', 'formulir_pmb')->get()
                ->each(function ($p) {
                    BerkasPrivat::hapus($p->bukti_bayar);
                    $p->delete();
                });

            $camaba->delete();

            if ($user = User::find($userId)) {
                $user->roles()->detach();
                $user->delete();
            }
        });
    }

    /** Penerima notifikasi pendaftar baru: admin + panitia aktif tahun PMB gelombang tersebut. */
    public function beritahuPendaftarBaru(Camaba $camaba): void
    {
        $tahun = optional($camaba->period)->tahun_pmb;

        $penerima = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->orWhereIn('id', PmbKepanitiaan::aktif()->where('tahun_pmb', $tahun)->select('user_id'))
            ->get();

        if ($penerima->isNotEmpty()) {
            Notification::send($penerima, new GeneralNotification(
                'Pendaftar PMB Baru',
                'Camaba baru atas nama ' . optional($camaba->user)->name . ' telah mendaftar.',
                route('admin.pmb.index'),
                'bi-person-badge-fill text-dark'
            ));
        }
    }

    private function nimBerikutnya(string $prefix): string
    {
        $lastNim = Mahasiswa::where('nim', 'like', $prefix . '%')->orderBy('nim', 'desc')->value('nim');
        $urutan = $lastNim ? ((int) substr($lastNim, strlen($prefix))) + 1 : 1;

        do {
            $nim = $prefix . str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
            $urutan++;
        } while (Mahasiswa::where('nim', $nim)->exists());

        return $nim;
    }
}
