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
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto_check_in' => 'required|image',
        ]);

        $user = $request->user();
        $today = Carbon::today();

        $absensiHariIni = AbsensiPegawai::where('user_id', $user->id)
            ->whereDate('tanggal_absensi', $today)
            ->first();

        if ($absensiHariIni && $absensiHariIni->waktu_check_in) {
            return response()->json(['message' => 'Anda sudah melakukan check-in hari ini.'], 422);
        }

        $lokasiKerja = LokasiKerja::all();
        $lokasiValid = null;

        foreach ($lokasiKerja as $lokasi) {
            $jarak = $this->hitungJarak(
                $request->latitude,
                $request->longitude,
                $lokasi->latitude,
                $lokasi->longitude
            );

            if ($jarak <= $lokasi->radius_toleransi_meter) {
                $lokasiValid = $lokasi;
                break;
            }
        }

        if (!$lokasiValid) {
            return response()->json(['message' => 'Anda tidak berada di lokasi kerja yang valid.'], 422);
        }

        $path = $request->file('foto_check_in')->store('foto-absensi', 'public');

        $absensi = AbsensiPegawai::updateOrCreate(
            ['user_id' => $user->id, 'tanggal_absensi' => $today],
            [
                'lokasi_kerja_id' => $lokasiValid->id,
                'waktu_check_in' => now(),
                'latitude_check_in' => $request->latitude,
                'longitude_check_in' => $request->longitude,
                'foto_check_in' => $path,
                'status_kehadiran' => 'Hadir',
            ]
        );

        // =================================================================
        // PERBAIKAN: Kirim respons dengan format camelCase yang konsisten
        // =================================================================
        return response()->json([
            'message' => 'Check-in berhasil.',
            'data' => [
                'check_in' => $absensi->waktu_check_in,
                'check_out' => $absensi->waktu_check_out,
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
            'foto_check_out' => 'required|image',
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

        // =================================================================
        // PERBAIKAN: Kirim respons dengan format camelCase yang konsisten
        // =================================================================
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
        $history = AbsensiPegawai::where('user_id', $request->user()->id)
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

        // =================================================================
        // PERBAIKAN: Kirim respons dengan format camelCase yang konsisten
        // =================================================================
        if ($status) {
            return response()->json([
                'check_in' => $status->waktu_check_in,
                'check_out' => $status->waktu_check_out,
            ]);
        }
        
        // Jika tidak ada data, kirim null agar frontend tahu
        return response()->json(null);
    }
    
    /**
     * Menghitung jarak antara dua titik koordinat (Haversine Formula).
     */
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

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