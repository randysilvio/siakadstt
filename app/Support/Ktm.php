<?php

namespace App\Support;

use App\Http\Controllers\VerifikasiDokumenController;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\TahunAkademik;

/**
 * Data Kartu Tanda Mahasiswa digital (E-KTM) — dipakai komponen web <x-ktm> dan API aplikasi.
 *
 * Desain KTM sengaja TIDAK mengikuti Theme Engine (Pengaturan › Tema): warna kartu adalah identitas resmi
 * kampus sehingga tetap sama walau tema SIAKAD diganti. Palet ini juga dipakai aplikasi seluler.
 */
class Ktm
{
    public const WARNA = [
        'latar'      => '#FDDFC1', // krem
        'oranye'     => '#F9A21B',
        'oranye_tua' => '#F47B16',
        'ungu'       => '#3D2350', // teks & kode QR
        'ungu_muda'  => '#5E3F82',
        'aktif'      => '#1F9D55',
        'nonaktif'   => '#B3402A',
    ];

    /** Gambar latar kartu (logo, judul, label, ornamen geometris & batik Papua). Sumber: resources/ktm/latar.py */
    public const LATAR = 'images/ktm/latar-ktm.jpg';

    /** Ukuran huruf nilai (px kanvas 1712) agar muat di garis isian selebar 460 px. */
    public static function ukuranTeks(string $teks): int
    {
        $n = mb_strlen($teks);
        return match (true) {
            $n <= 16 => 36,
            $n <= 21 => 33,
            $n <= 25 => 30,
            $n <= 30 => 26,
            default  => 23,
        };
    }

    public static function data(Mahasiswa $mahasiswa): array
    {
        $mahasiswa->loadMissing('programStudi');
        $token = VerifikasiDokumenController::buatTokenDokumen('AKTIF', (int) $mahasiswa->id);
        $ta = TahunAkademik::where('is_active', true)->first();

        return [
            'nama'           => $mahasiswa->nama_lengkap,
            'nim'            => $mahasiswa->nim,
            'prodi'          => optional($mahasiswa->programStudi)->nama_prodi ?? '-',
            'angkatan'       => (string) $mahasiswa->tahun_masuk,
            'status'         => (string) $mahasiswa->status_mahasiswa,
            'aktif'          => $mahasiswa->status_mahasiswa === 'Aktif',
            'foto_url'       => $mahasiswa->foto_profil ? asset('storage/' . $mahasiswa->foto_profil) : null,
            'semester'       => optional($ta)->nama_semester,
            'token'          => $token,
            'verifikasi_url' => route('verifikasi.dokumen', $token),
            'kampus'         => [
                'nama'      => Pengaturan::get('nama_kampus', 'Sekolah Tinggi Teologi Gereja Protestan Indonesia Papua'),
                'singkatan' => Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA'),
                'kota'      => Pengaturan::get('kota_domisili', 'Fakfak'),
                'logo_url'  => Pengaturan::logoUrl(),
            ],
            'warna'          => self::WARNA,
            'latar_url'      => asset(self::LATAR),
        ];
    }
}
