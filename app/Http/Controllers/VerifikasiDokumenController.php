<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\PengajuanSurat;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiDokumenController extends Controller
{
    public static function buatTokenDokumen(string $jenis, int $idEntitas): string
    {
        $payload = strtoupper($jenis) . '-' . $idEntitas;
        $hmac = substr(hash_hmac('sha256', $payload, config('app.key', 'sttgpipapua')), 0, 12);
        return $payload . '-' . strtoupper($hmac);
    }

    public function verify(string $token): View
    {
        $isValid = false;
        $dataDokumen = null;
        $singkatanPt = Pengaturan::get('singkatan_kampus', 'STT GPI PAPUA');

        $suratMandiri = PengajuanSurat::with('mahasiswa.programStudi')
            ->where('kode_verifikasi', $token)
            ->where('status', 'Disetujui')
            ->first();

        if ($suratMandiri) {
            $isValid = true;
            $dataDokumen = [
                'jenis_dokumen' => strtoupper($suratMandiri->jenis_surat),
                'nomor_dokumen' => ($suratMandiri->nomor_surat ?: str_pad((string) $suratMandiri->id, 3, '0', STR_PAD_LEFT)) . '/' . strtoupper(str_replace(' ', '-', $singkatanPt)) . '/AK/' . $suratMandiri->created_at->format('Y'),
                'nama_pemilik'  => $suratMandiri->mahasiswa->nama_lengkap ?? '-',
                'nim_identitas' => $suratMandiri->mahasiswa->nim ?? '-',
                'program_studi' => optional(optional($suratMandiri->mahasiswa)->programStudi)->nama_prodi ?? '-',
                'tanggal_terbit'=> ($suratMandiri->tanggal_disetujui ?? $suratMandiri->updated_at)->translatedFormat('d F Y'),
                'keterangan'    => 'Keperluan: ' . $suratMandiri->keperluan,
                'kode_hash'     => strtoupper($token),
            ];
        } else {
            $parts = explode('-', strtoupper($token));
            if (count($parts) === 3) {
                [$jenis, $idStr, $hmacInput] = $parts;
                $idEntitas = (int) $idStr;
                $expectedToken = self::buatTokenDokumen($jenis, $idEntitas);

                if (hash_equals($expectedToken, strtoupper($token))) {
                    $mhs = Mahasiswa::with('programStudi')->find($idEntitas);
                    if ($mhs) {
                        $isValid = true;
                        $mapJudul = [
                            'KRS'        => 'KARTU RENCANA STUDI (KRS) AKADEMIK',
                            'KHS'        => 'KARTU HASIL STUDI (KHS) SEMESTER',
                            'TRANSKRIP'  => 'TRANSKRIP NILAI AKADEMIK RESMI',
                            'SURATAKTIF' => 'SURAT KETERANGAN AKTIF KULIAH',
                            'AKTIF'      => 'KARTU TANDA MAHASISWA DIGITAL (E-KTM)',
                        ];
                        $dataDokumen = [
                            'jenis_dokumen' => $mapJudul[$jenis] ?? 'DOKUMEN AKADEMIK RESMI',
                            'nomor_dokumen' => 'REG-' . $jenis . '/' . $mhs->nim . '/' . date('Y'),
                            'nama_pemilik'  => $mhs->nama_lengkap,
                            'nim_identitas' => $mhs->nim,
                            'program_studi' => optional($mhs->programStudi)->nama_prodi ?? '-',
                            'tanggal_terbit'=> now()->translatedFormat('d F Y'),
                            'keterangan'    => 'Status Mahasiswa: ' . strtoupper($mhs->status_mahasiswa) . ' | IPK Kumulatif: ' . number_format($mhs->hitungIpk(), 2),
                            'kode_hash'     => $expectedToken,
                        ];
                    }
                }
            }
        }

        return view('public.verifikasi_dokumen', compact('isValid', 'dataDokumen', 'token'));
    }
}