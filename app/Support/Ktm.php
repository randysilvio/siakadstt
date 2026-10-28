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

    /**
     * Tata letak data di atas latar (satuan px kanvas 1712 x 1080). x = tepi kiri, b = garis dasar teks,
     * w = lebar maksimal; ukuran huruf mengecil otomatis dalam rentang [min, maks] agar muat satu baris.
     * Nilai yang sama dipakai aplikasi (src/ktm/desain.ts).
     */
    public const TATA_LETAK = [
        'foto'     => ['x' => 120, 'y' => 250, 'w' => 420, 'h' => 540],
        'nama'     => ['x' => 600, 'b' => 472, 'w' => 820, 'maks' => 52, 'min' => 28],
        'nim'      => ['x' => 600, 'b' => 600, 'w' => 820, 'maks' => 42, 'min' => 42],
        'prodi'    => ['x' => 600, 'b' => 730, 'w' => 500, 'maks' => 34, 'min' => 22],
        'angkatan' => ['x' => 1130, 'b' => 730, 'w' => 290, 'maks' => 34, 'min' => 26],
        'token'    => ['x' => 600, 'b' => 808, 'w' => 820, 'maks' => 22, 'min' => 22],
        'qr'       => ['x' => 1472, 'y' => 660, 's' => 174],
    ];

    /** Ukuran huruf (px kanvas) agar teks muat dalam lebar w — rata-rata lebar huruf Poppins tebal ≈ 0,62 em. */
    public static function ukuranTeks(string $teks, int $lebar, int $maks, int $min): int
    {
        $n = max(1, mb_strlen($teks));
        return max($min, min($maks, (int) floor($lebar / (0.62 * $n))));
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
