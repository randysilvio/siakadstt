<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\DokumenTerbit;
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

        // 1. Dokumen bercetak dengan kode terikat isi (format baru)
        $terbit = DokumenTerbit::with('mahasiswa.programStudi')->where('kode', strtoupper(trim($token)))->first();
        if ($terbit && $terbit->utuh()) {
            $r = $terbit->ringkasan;
            $mhs = $terbit->mahasiswa;
            $rincian = $r['nilai'] ?? $r['mata_kuliah'] ?? [];
            $keterangan = match ($terbit->jenis) {
                'KHS'        => 'Semester ' . ($r['semester'] ?? '-') . ' | IPS ' . ($r['ips'] ?? '-') . ' | ' . ($r['total_sks'] ?? '-') . ' SKS',
                'TRANSKRIP'  => 'IPK ' . ($r['ipk'] ?? '-') . ' | Total ' . ($r['total_sks'] ?? '-') . ' SKS',
                'KRS'        => 'Semester ' . ($r['semester'] ?? '-') . ' | Status KRS: ' . strtoupper($r['status'] ?? '-'),
                'SURATAKTIF' => 'Semester ' . ($r['semester'] ?? '-') . ' | Keperluan: ' . ($r['keperluan'] ?? '-'),
                default      => '-',
            };

            return view('public.verifikasi_dokumen', [
                'isValid'     => true,
                'token'       => $token,
                'dataDokumen' => [
                    'jenis_dokumen'  => DokumenTerbit::JUDUL[$terbit->jenis] ?? 'DOKUMEN AKADEMIK RESMI',
                    'nomor_dokumen'  => $r['nomor_surat'] ?? $terbit->kode,
                    'nama_pemilik'   => optional($mhs)->nama_lengkap ?? '-',
                    'nim_identitas'  => optional($mhs)->nim ?? '-',
                    'program_studi'  => optional(optional($mhs)->programStudi)->nama_prodi ?? '-',
                    'tanggal_terbit' => $terbit->created_at->translatedFormat('d F Y H:i'),
                    'keterangan'     => $keterangan . '. Isi di bawah adalah isi dokumen saat dicetak; bandingkan dengan dokumen yang Anda terima.',
                    'kode_hash'      => strtoupper(substr($terbit->sidik, 0, 32)),
                    'rincian'        => $rincian,
                ],
            ]);
        }

        $suratMandiri = PengajuanSurat::with('mahasiswa.programStudi')
            ->where('kode_verifikasi', $token)
            ->where('status', 'Disetujui')
            ->first();

        if ($suratMandiri) {
            $isValid = true;
            $rawNomor = trim((string) $suratMandiri->nomor_surat);
            if (!empty($rawNomor) && str_contains($rawNomor, '/')) {
                $noSuratFormatted = $rawNomor;
            } else {
                $urut = !empty($rawNomor) ? $rawNomor : str_pad((string) $suratMandiri->id, 3, '0', STR_PAD_LEFT);
                $noSuratFormatted = $urut . '/' . strtoupper(str_replace(' ', '-', $singkatanPt)) . '/AK/' . $suratMandiri->created_at->format('Y');
            }

            $tglTerbit = $suratMandiri->tanggal_disetujui ?? $suratMandiri->updated_at ?? now();

            $dataDokumen = [
                'jenis_dokumen' => strtoupper($suratMandiri->jenis_surat),
                'nomor_dokumen' => $noSuratFormatted,
                'nama_pemilik'  => $suratMandiri->mahasiswa->nama_lengkap ?? '-',
                'nim_identitas' => $suratMandiri->mahasiswa->nim ?? '-',
                'program_studi' => optional(optional($suratMandiri->mahasiswa)->programStudi)->nama_prodi ?? '-',
                'tanggal_terbit'=> \Carbon\Carbon::parse($tglTerbit)->translatedFormat('d F Y'),
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
                            'keterangan'    => 'Kode format lama: hanya memastikan identitas mahasiswa, BUKAN isi dokumen. Status saat ini: ' . strtoupper($mhs->status_mahasiswa) . '. Minta dokumen cetak ulang untuk verifikasi isi.',
                            'kode_hash'     => $expectedToken,
                        ];
                    }
                }
            }
        }

        return view('public.verifikasi_dokumen', compact('isValid', 'dataDokumen', 'token'));
    }
}