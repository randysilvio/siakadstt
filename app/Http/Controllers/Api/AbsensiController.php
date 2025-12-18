<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPegawai;
use App\Models\LokasiKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    /**
     * Menangani permintaan check-in dari pegawai.
     */
    public function checkIn(Request $request)
    {
        // PERBAIKAN 1: Tambah validasi max ukuran file (5MB) agar tidak error saat upload dari HP
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto_check_in' => 'required|image|max:5120', 
        ]);

        $user = $request->user();
        $today = Carbon::today();

        // Cek apakah user sudah absen hari ini
        $absensiHariIni = AbsensiPegawai::where('user_id', $user->id)
            ->whereDate('tanggal_absensi', $today)
            ->first();

        if ($absensiHariIni && $absensiHariIni->waktu_check_in) {
            return response()->json(['message' => 'Anda sudah melakukan check-in hari ini.'], 422);
        }

        // PERBAIKAN 2: Logika Cek Jarak dengan Debugging Info
        $lokasiKerja = LokasiKerja::all();
        $lokasiValid = null;
        
        // Variabel untuk menyimpan info debugging jika gagal
        $jarakTerdekat = 999999999; 
        $namaLokasiTerdekat = '-';
        $radiusLokasiTerdekat = 0;

        // Pastikan koordinat dari request dibaca sebagai float
        $latUser = (float) $request->latitude;
        $longUser = (float) $request->longitude;

        foreach ($lokasiKerja as $lokasi) {
            $jarak = $this->hitungJarak(
                $latUser,
                $longUser,
                (float) $lokasi->latitude,
                (float) $lokasi->longitude
            );

            // Simpan data lokasi terdekat untuk pesan error yang informatif
            if ($jarak < $jarakTerdekat) {
                $jarakTerdekat = $jarak;
                $namaLokasiTerdekat = $lokasi->nama_lokasi;
                $radiusLokasiTerdekat = $lokasi->radius_toleransi_meter;
            }

            // Cek apakah user berada dalam radius lokasi ini
            if ($jarak <= $lokasi->radius_toleransi_meter) {
                $lokasiValid = $lokasi;
                break; // Ketemu lokasi valid, hentikan loop
            }
        }

        // Jika tidak ada lokasi yang cocok, kirim respon error detail
        if (!$lokasiValid) {
            return response()->json([
                'message' => 'Posisi Anda diluar jangkauan lokasi kerja.',
                'debug_info' => [
                    'jarak_terdeteksi' => round($jarakTerdekat, 2) . ' meter',
                    'lokasi_terdekat' => $namaLokasiTerdekat,
                    'radius_diizinkan' => $radiusLokasiTerdekat . ' meter',
                    'selisih' => round($jarakTerdekat - $radiusLokasiTerdekat, 2) . ' meter (Anda terlalu jauh)'
                ]
            ], 422);
        }

        // Simpan Foto
        $path = $request->file('foto_check_in')->store('foto-absensi', 'public');

        // Simpan Data Absensi
        $absensi = AbsensiPegawai::updateOrCreate(
            ['user_id' => $user->id, 'tanggal_absensi' => $today],
            [
                'lokasi_kerja_id' => $lokasiValid->id,
                'waktu_check_in' => now(),
                'latitude_check_in' => $latUser,
                'longitude_check_in' => $longUser,
                'foto_check_in' => $path,
                'status_kehadiran' => 'Hadir',
            ]
        );

        return response()->json([
            'message' => 'Check-in berhasil.',
            'data' => [
                'check_in' => $absensi->waktu_check_in,
                'lokasi' => $lokasiValid->nama_lokasi,
            ],
        ], 201);
    }

    /**
     * Menangani permintaan check-out dari pegawai.
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto_check_out' => 'required|image|max:5120', // Tambah validasi max 5MB
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
    
    /**
     * Mengambil riwayat absensi pengguna.
     */
    public function getHistory(Request $request)
    {
        $history = AbsensiPegawai::with('lokasiKerja') // Load relasi lokasi biar lengkap
            ->where('user_id', $request->user()->id)
            ->orderBy('tanggal_absensi', 'desc')
            ->paginate(15);

        return response()->json($history);
    }
    
    /**
     * Mendapatkan status absensi hari ini.
     */
    public function getStatusHariIni(Request $request)
    {
        $status = AbsensiPegawai::where('user_id', $request->user()->id)
            ->whereDate('tanggal_absensi', Carbon::today())
            ->first();

        if ($status) {
            return response()->json([
                'check_in' => $status->waktu_check_in,
                'check_out' => $status->waktu_check_out,
            ]);
        }
        
        return response()->json(null);
    }
    
    /**
     * Menghitung jarak antara dua titik koordinat (Haversine Formula).
     * Hasil dalam satuan Meter.
     */
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}