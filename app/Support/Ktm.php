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
        'latar'       => '#0B2545',
        'latar_ujung' => '#16386B',
        'emas'        => '#C9A227',
        'emas_muda'   => '#E9D48A',
        'teks'        => '#FFFFFF',
        'teks_redup'  => '#C7D2E3',
        'aktif'       => '#1F9D55',
        'nonaktif'    => '#B45309',
    ];

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
        ];
    }
}
