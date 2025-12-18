<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPegawai;
use App\Models\LokasiKerja;
// [TAMBAHAN BARU] Import Model Pengaturan
use App\Models\PengaturanAbsensi; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    // =========================================================================
    // 1. FITUR ABSENSI (Check-in, Check-out, History, Radius)
    // =========================================================================

    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto_check_in' => 'required|image|max:5120', // Max 5MB
        ]);

        $user = $request->user();
        $today = Carbon::today();

        $absensiHariIni = AbsensiPegawai::where('user_id', $user->id)
            ->whereDate('tanggal_absensi', $today)
            ->first();

        if ($absensiHariIni && $absensiHariIni->waktu_check_in) {
            return response()->json(['message' => 'Anda sudah melakukan check-in hari ini.'], 422);
        }

        // =========================================================
        // [TAMBAHAN BARU] LOGIKA CEK JAM MASUK & TOLERANSI
        // =========================================================
        // 1. Ambil Pengaturan dari Database (default jika kosong)
        $setting = PengaturanAbsensi::first();
        $jamMasuk = $setting ? $setting->jam_masuk : '08:00:00';
        $toleransi = $setting ? $setting->toleransi_terlambat_menit : 15;

        // 2. Hitung Batas Waktu (Jam Masuk + Toleransi)
        // Carbon createFromFormat menggunakan tanggal hari ini secara otomatis
        $batasWaktu = Carbon::createFromFormat('H:i:s', $jamMasuk)->addMinutes($toleransi);
        $waktuSekarang = Carbon::now();

        // 3. Tentukan Status
        $statusKehadiran = 'Hadir'; // Default
        $pesanResponse = 'Check-in berhasil.';

        // Jika waktu sekarang LEBIH BESAR dari batas waktu -> ALPHA
        if ($waktuSekarang->format('H:i:s') > $batasWaktu->format('H:i:s')) {
            $statusKehadiran = 'Alpha';
            $pesanResponse = 'Absen diterima, namun status Anda ALPHA karena terlambat melebihi toleransi.';
        }
        // =========================================================

        // Cek Lokasi (Logika Radius)
        $lokasiKerja = LokasiKerja::all();
        $lokasiValid = null;
        $jarakTerdekat = 999999999;
        
        $latUser = (float) $request->latitude;
        $longUser = (float) $request->longitude;

        foreach ($lokasiKerja as $lokasi) {
            $jarak = $this->hitungJarak(
                $latUser, $longUser,
                (float) $lokasi->latitude, (float) $lokasi->longitude
            );

            if ($jarak < $jarakTerdekat) $jarakTerdekat = $jarak;

            if ($jarak <= $lokasi->radius_toleransi_meter) {
                $lokasiValid = $lokasi;
                break;
            }
        }

        if (!$lokasiValid) {
            return response()->json([
                'message' => 'Posisi Anda diluar jangkauan lokasi kerja.',
                'debug_info' => ['jarak_terdeteksi' => round($jarakTerdekat, 2) . ' meter']
            ], 422);
        }

        $path = $request->file('foto_check_in')->store('foto-absensi', 'public');

        // Simpan Data dengan Status yang sudah dihitung (Hadir/Alpha)
        $absensi = AbsensiPegawai::updateOrCreate(
            ['user_id' => $user->id, 'tanggal_absensi' => $today],
            [
                'lokasi_kerja_id' => $lokasiValid->id,
                'waktu_check_in' => now(),
                'latitude_check_in' => $latUser,
                'longitude_check_in' => $longUser,
                'foto_check_in' => $path,
                'status_kehadiran' => $statusKehadiran, // <--- Menggunakan variabel dinamis
            ]
        );

        return response()->json([
            'message' => $pesanResponse,
            'data' => [
                'check_in' => $absensi->waktu_check_in,
                'lokasi' => $lokasiValid->nama_lokasi,
                'status' => $statusKehadiran, // Kirim balik status agar user tahu
            ],
        ], 201);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto_check_out' => 'required|image|max:5120',
        ]);

        $user = $request->user();
        $absensiHariIni = AbsensiPegawai::where('user_id', $user->id)
            ->whereDate('tanggal_absensi', Carbon::today())
            ->first();

        if (!$absensiHariIni || !$absensiHariIni->waktu_check_in) {
            return response()->json(['message' => 'Anda belum melakukan check-in hari ini.'], 422);
        }
        
        if ($absensiHariIni->waktu_check_out) {
            return response()->json(['message' => 'Anda sudah melakukan check-out hari ini.'], 422);
        }

        $path = $request->file('foto_check_out')->store('foto-absensi', 'public');

        $absensiHariIni->update([
            'waktu_check_out' => now(),
            'latitude_check_out' => $request->latitude,
            'longitude_check_out' => $request->longitude,
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

    // =========================================================================
    // 2. FITUR KEUANGAN (Mahasiswa)
    // =========================================================================
    
    public function riwayatPembayaranMahasiswa(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasRole('mahasiswa') || !$user->mahasiswa) {
            return response()->json([]);
        }

        $pembayarans = $user->mahasiswa->pembayarans()
            ->orderBy('semester', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'semester' => $item->semester,
                    'jumlah' => $item->jumlah,
                    'status' => $item->status,
                    'tanggal' => $item->tanggal_bayar ? date('d M Y', strtotime($item->tanggal_bayar)) : '-'
                ];
            });

        return response()->json($pembayarans);
    }

    // =========================================================================
    // 3. FITUR KHS / NILAI (Mahasiswa)
    // =========================================================================

    public function getKhsMahasiswa(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('mahasiswa') || !$user->mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan'], 404);
        }

        $mahasiswa = $user->mahasiswa;
        
        $transkrip = $mahasiswa->mataKuliahs()
            ->wherePivotNotNull('nilai')
            ->orderBy('pivot_tahun_akademik_id', 'desc') 
            ->get()
            ->groupBy(function($item) {
                return $item->pivot->tahun_akademik_id;
            });

        $hasil = [];
        foreach ($transkrip as $tahunId => $matkuls) {
            $dataIps = $mahasiswa->hitungIps($tahunId); 
            
            $listMatkul = $matkuls->map(function($mk) {
                return [
                    'kode' => $mk->kode_mk,
                    'nama' => $mk->nama_mk,
                    'sks' => $mk->sks,
                    'nilai' => $mk->pivot->nilai,
                ];
            });

            $hasil[] = [
                'semester_id' => $tahunId,
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

    // =========================================================================
    // HELPER
    // =========================================================================
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