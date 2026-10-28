<?php

namespace App\Http\Controllers\Api;

use App\Support\BerkasPrivat;
use App\Http\Controllers\Controller;
use App\Models\AbsensiPegawai;
use App\Models\LokasiKerja;
use App\Models\PengaturanAbsensi; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    /**
     * Helper untuk mengecek apakah titik koordinat berada dalam radius lokasi kerja.
     */
    private function cariLokasiKerjaValid(float $latUser, float $longUser): array
    {
        $lokasiKerja = LokasiKerja::all();
        $lokasiValid = null;
        $jarakTerdekat = 999999999;

        foreach ($lokasiKerja as $lokasi) {
            $jarak = $this->hitungJarak(
                $latUser,
                $longUser,
                (float) $lokasi->latitude,
                (float) $lokasi->longitude
            );

            if ($jarak < $jarakTerdekat) {
                $jarakTerdekat = $jarak;
            }

            if ($jarak <= $lokasi->radius_toleransi_meter) {
                $lokasiValid = $lokasi;
                break;
            }
        }

        return ['lokasi' => $lokasiValid, 'jarak_terdekat' => $jarakTerdekat];
    }

    public function checkIn(Request $request)
    {
        $user = $request->user();

        // [PERBAIKAN KEAMANAN FASE 6]: Tolak mahasiswa/camaba dari fitur absensi pegawai
        if ($user->hasRole(['mahasiswa', 'camaba']) && !$user->hasRole(['dosen', 'admin', 'pegawai', 'keuangan', 'pustakawan', 'administrasi_umum'])) {
            return response()->json(['message' => 'Fitur absensi kerja hanya diperuntukkan bagi Dosen dan Tenaga Kependidikan.'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto_check_in' => 'required|image|max:5120',
            'is_mocked' => 'nullable|boolean',
        ]);

        // Aplikasi seluler mengirim is_mocked = true bila Android mendeteksi lokasi palsu (fake GPS)
        if ($request->boolean('is_mocked')) {
            return response()->json(['message' => 'Absensi ditolak: perangkat terdeteksi memakai lokasi palsu (fake GPS). Matikan aplikasi pengubah lokasi lalu coba lagi.'], 422);
        }

        $today = Carbon::today();

        $absensiHariIni = AbsensiPegawai::where('user_id', $user->id)
            ->whereDate('tanggal_absensi', $today)
            ->first();

        if ($absensiHariIni && $absensiHariIni->waktu_check_in) {
            return response()->json(['message' => 'Anda sudah melakukan check-in hari ini.'], 422);
        }

        // Cek Jam Masuk & Toleransi
        $setting = PengaturanAbsensi::first();
        $jamMasuk = $setting ? $setting->jam_masuk : '08:00:00';
        $toleransi = $setting ? (int) $setting->toleransi_terlambat_menit : 15;

        $batasWaktu = Carbon::parse($jamMasuk) /* jam dari input <time> bisa "08:00" tanpa detik */->addMinutes($toleransi);
        $waktuSekarang = Carbon::now();

        $statusKehadiran = 'Hadir'; 
        $keterangan = 'Tepat Waktu';
        $pesanResponse = 'Check-in berhasil.';

        if ($waktuSekarang->format('H:i:s') > $batasWaktu->format('H:i:s')) {
            $keterangan = 'Terlambat (Melewati batas toleransi ' . $batasWaktu->format('H:i') . ')';
            $pesanResponse = 'Check-in berhasil dicatat dengan status Hadir (Terlambat).';
        }

        // Cek Lokasi (Logika Radius)
        $latUser = (float) $request->latitude;
        $longUser = (float) $request->longitude;
        $cekLokasi = $this->cariLokasiKerjaValid($latUser, $longUser);
        $lokasiValid = $cekLokasi['lokasi'];

        if (!$lokasiValid) {
            return response()->json([
                'message' => 'Posisi Anda berada di luar jangkauan radius lokasi kerja kampus.',
                'debug_info' => ['jarak_terdeteksi' => round($cekLokasi['jarak_terdekat'], 2) . ' meter']
            ], 422);
        }

        $path = BerkasPrivat::simpan($request->file('foto_check_in'), 'foto-absensi');

        $absensi = AbsensiPegawai::updateOrCreate(
            ['user_id' => $user->id, 'tanggal_absensi' => $today],
            [
                'lokasi_kerja_id' => $lokasiValid->id,
                'waktu_check_in' => now(),
                'latitude_check_in' => $latUser,
                'longitude_check_in' => $longUser,
                'foto_check_in' => $path,
                'status_kehadiran' => $statusKehadiran,
                'keterangan' => $keterangan,
            ]
        );

        return response()->json([
            'message' => $pesanResponse,
            'data' => [
                'check_in' => $absensi->waktu_check_in,
                'lokasi' => $lokasiValid->nama_lokasi,
                'status' => $statusKehadiran,
                'keterangan' => $keterangan,
            ],
        ], 201);
    }

    public function checkOut(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole(['mahasiswa', 'camaba']) && !$user->hasRole(['dosen', 'admin', 'pegawai', 'keuangan', 'pustakawan', 'administrasi_umum'])) {
            return response()->json(['message' => 'Fitur absensi kerja hanya diperuntukkan bagi Dosen dan Tenaga Kependidikan.'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto_check_out' => 'required|image|max:5120',
            'is_mocked' => 'nullable|boolean',
        ]);

        // Aplikasi seluler mengirim is_mocked = true bila Android mendeteksi lokasi palsu (fake GPS)
        if ($request->boolean('is_mocked')) {
            return response()->json(['message' => 'Absensi ditolak: perangkat terdeteksi memakai lokasi palsu (fake GPS). Matikan aplikasi pengubah lokasi lalu coba lagi.'], 422);
        }

        $absensiHariIni = AbsensiPegawai::where('user_id', $user->id)
            ->whereDate('tanggal_absensi', Carbon::today())
            ->first();

        if (!$absensiHariIni || !$absensiHariIni->waktu_check_in) {
            return response()->json(['message' => 'Anda belum melakukan check-in hari ini.'], 422);
        }
        
        if ($absensiHariIni->waktu_check_out) {
            return response()->json(['message' => 'Anda sudah melakukan check-out hari ini.'], 422);
        }

        // [PERBAIKAN KEAMANAN FASE 6]: Validasi radius lokasi kerja saat Check-Out
        $latUser = (float) $request->latitude;
        $longUser = (float) $request->longitude;
        $cekLokasi = $this->cariLokasiKerjaValid($latUser, $longUser);

        if (!$cekLokasi['lokasi']) {
            return response()->json([
                'message' => 'Check-out ditolak: Posisi Anda berada di luar jangkauan radius lokasi kerja kampus.',
                'debug_info' => ['jarak_terdeteksi' => round($cekLokasi['jarak_terdekat'], 2) . ' meter']
            ], 422);
        }

        $path = BerkasPrivat::simpan($request->file('foto_check_out'), 'foto-absensi');

        $absensiHariIni->update([
            'waktu_check_out' => now(),
            'latitude_check_out' => $latUser,
            'longitude_check_out' => $longUser,
            'foto_check_out' => $path,
        ]);

        return response()->json([
            'message' => 'Check-out berhasil.',
            'data' => [
                'check_in' => $absensiHariIni->waktu_check_in,
                'check_out' => $absensiHariIni->waktu_check_out,
            ],
        ]);
    }
    
    public function getHistory(Request $request)
    {
        $history = AbsensiPegawai::with('lokasiKerja')
            ->where('user_id', $request->user()->id)
            ->orderBy('tanggal_absensi', 'desc')
            ->paginate(15);
        return response()->json($history);
    }
    
    public function getStatusHariIni(Request $request)
    {
        $status = AbsensiPegawai::where('user_id', $request->user()->id)
            ->whereDate('tanggal_absensi', Carbon::today())
            ->first();

        if ($status) {
            return response()->json([
                'check_in' => $status->waktu_check_in,
                'check_out' => $status->waktu_check_out,
                'status_kehadiran' => $status->status_kehadiran,
            ]);
        }
        return response()->json(null);
    }

    public function riwayatPembayaranMahasiswa(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasRole('mahasiswa') || !$user->mahasiswa) {
            return response()->json([]);
        }

        $mahasiswa = $user->mahasiswa;
        $pembayarans = \App\Models\Pembayaran::where(fn ($q) => $q->where('user_id', $user->id)->orWhere('mahasiswa_id', $mahasiswa->id))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'semester'   => $item->semester,
                    'jenis'      => $item->jenis_pembayaran,
                    'jumlah'     => $item->jumlah,
                    'status'     => $item->status,
                    'keterangan' => $item->keterangan,
                    'ada_bukti'  => (bool) $item->bukti_bayar,
                    'tanggal'    => $item->tanggal_bayar ? date('d M Y', strtotime($item->tanggal_bayar)) : '-',
                ];
            });

        return response()->json($pembayarans);
    }

    /**
     * Unggah bukti transfer dari aplikasi seluler (sama dengan unggah di web): hanya pemilik tagihan,
     * gambar JPG/PNG maks. 2 MB, status menjadi menunggu_konfirmasi sampai diverifikasi Bagian Keuangan.
     */
    public function unggahBuktiPembayaran(Request $request, \App\Models\Pembayaran $pembayaran)
    {
        $user = $request->user();
        $mahasiswa = $user->mahasiswa;
        if (!$user->hasRole('mahasiswa') || !$mahasiswa) {
            return response()->json(['message' => 'Hanya mahasiswa yang dapat mengunggah bukti pembayaran.'], 403);
        }
        if ((int) $pembayaran->user_id !== (int) $user->id && (int) $pembayaran->mahasiswa_id !== (int) $mahasiswa->id) {
            return response()->json(['message' => 'Anda tidak berhak mengunggah bukti untuk tagihan ini.'], 403);
        }
        if (in_array($pembayaran->status, ['lunas', 'dispensasi'], true)) {
            return response()->json(['message' => 'Tagihan ini sudah diverifikasi, bukti tidak perlu diunggah lagi.'], 422);
        }

        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'bukti_bayar.required' => 'Silakan pilih foto/screenshot bukti transfer.',
            'bukti_bayar.image'    => 'File bukti transfer harus berupa gambar (JPG/PNG).',
            'bukti_bayar.mimes'    => 'File bukti transfer harus berupa gambar (JPG/PNG).',
            'bukti_bayar.max'      => 'Ukuran file bukti transfer maksimal 2MB.',
        ]);

        \App\Support\BerkasPrivat::hapus($pembayaran->bukti_bayar);
        $pembayaran->update([
            'bukti_bayar'   => \App\Support\BerkasPrivat::simpan($request->file('bukti_bayar'), 'bukti-bayar-spp'),
            'status'        => 'menunggu_konfirmasi',
            'tanggal_bayar' => now(),
        ]);

        return response()->json([
            'message' => 'Bukti pembayaran berhasil diunggah. Mohon tunggu verifikasi dari Bagian Keuangan.',
            'status'  => 'menunggu_konfirmasi',
        ]);
    }

    public function getKhsMahasiswa(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('mahasiswa') || !$user->mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan'], 404);
        }

        $mahasiswa = $user->mahasiswa;

        if ($mahasiswa->status_evaluasi === 'belum_isi') {
            return response()->json(['message' => 'Evaluasi dosen (EDOM) belum diisi'], 403);
        }
        
        $transkrip = $mahasiswa->mataKuliahs()
            ->withPivot('nilai', 'tahun_akademik_id') 
            ->orderBy('pivot_tahun_akademik_id', 'desc') 
            ->get()
            ->groupBy(function ($item) {
                return $item->pivot->tahun_akademik_id;
            });

        $hasil = [];
        foreach ($transkrip as $tahunId => $matkuls) {
            $dataIps = $mahasiswa->hitungIps($tahunId); 
            
            $listMatkul = $matkuls->map(function ($mk) {
                return [
                    'kode' => $mk->kode_mk,
                    'nama' => $mk->nama_mk,
                    'sks' => $mk->sks,
                    'nilai' => $mk->pivot->nilai === null ? '-' : $mk->pivot->nilai,
                ];
            });

            $ta = \App\Models\TahunAkademik::withTrashed()->find($tahunId);
            $hasil[] = [
                'semester_id' => $tahunId,
                'semester' => $ta ? $ta->nama_semester : '-',
                'ips' => $dataIps['ips'],
                'total_sks' => $dataIps['total_sks'],
                'mata_kuliah' => $listMatkul
            ];
        }

        return response()->json([
            'mahasiswa' => [
                'nama' => $mahasiswa->nama_lengkap,
                'nim' => $mahasiswa->nim,
                'prodi' => $mahasiswa->programStudi->nama_prodi ?? '-',
                'ipk' => $mahasiswa->hitungIpk(),
                'total_sks_lulus' => $mahasiswa->totalSksLulus()
            ],
            'khs' => $hasil
        ]);
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; 
        $latFrom = deg2rad($lat1); $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2); $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}