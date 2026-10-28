<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CetakController;
use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Unduh dokumen PDF resmi mahasiswa dari aplikasi (memakai CetakController yang sama dengan web,
 * sehingga isi, kode verifikasi, dan syaratnya identik). Syarat yang di web berupa redirect dikembalikan sebagai JSON.
 */
class DokumenController extends Controller
{
    public function unduh(Request $request, string $jenis)
    {
        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa) {
            return response()->json(['message' => 'Dokumen ini hanya untuk mahasiswa.'], 403);
        }

        $tolak = fn (string $pesan) => response()->json(['message' => $pesan], 422);
        $cetak = app(CetakController::class);

        switch ($jenis) {
            case 'krs':
                $ta = TahunAkademik::where('is_active', true)->first();
                if (!$ta) {
                    return $tolak('Belum ada Tahun Akademik yang aktif.');
                }
                if (!$mahasiswa->krsDisetujuiPada($ta->id)) {
                    return $tolak('KRS final hanya dapat dicetak setelah disetujui Dosen Wali / Kaprodi. Status KRS Anda: ' . strtoupper($mahasiswa->statusKrsPada($ta->id)) . '.');
                }
                $hasil = $cetak->cetakKrs();
                break;

            case 'khs':
                if ($mahasiswa->status_evaluasi === 'belum_isi') {
                    return $tolak('Selesaikan Evaluasi Dosen (EDOM) semester ini sebelum mencetak KHS.');
                }
                $request->validate(['tahun_akademik_id' => 'required|integer|exists:tahun_akademiks,id']);
                $hasil = $cetak->cetakKhs($request);
                break;

            case 'transkrip':
                if ($mahasiswa->status_mahasiswa === 'Lulus' && !$mahasiswa->sudah_isi_tracer) {
                    return $tolak('Lengkapi kuesioner Tracer Study Alumni terlebih dahulu sebelum mencetak Transkrip.');
                }
                if ($mahasiswa->status_mahasiswa !== 'Lulus' && $mahasiswa->status_evaluasi === 'belum_isi') {
                    return $tolak('Selesaikan Evaluasi Dosen (EDOM) semester ini sebelum mencetak Transkrip.');
                }
                $hasil = $cetak->cetakTranskrip();
                break;

            case 'jadwal':
                if (!TahunAkademik::where('is_active', true)->exists()) {
                    return $tolak('Belum ada Tahun Akademik yang aktif.');
                }
                $hasil = $cetak->cetakJadwal();
                break;

            default:
                abort(404);
        }

        if ($hasil instanceof RedirectResponse) {
            return $tolak(optional($hasil->getSession())->get('error') ?: 'Dokumen belum dapat dicetak.');
        }

        return $hasil;
    }
}
