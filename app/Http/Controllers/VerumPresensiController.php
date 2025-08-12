<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumPresensi;
use App\Models\VerumKehadiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerumPresensiController extends Controller
{
    /**
     * Dosen membuka sesi presensi baru.
     */
    public function store(Request $request, VerumKelas $verum_kela)
    {
        $request->validate([
            'judul_pertemuan' => 'required|string|max:255',
            'pertemuan_ke' => 'required|integer|min:1',
            'durasi' => 'required|integer|min:1', // Durasi dalam menit
        ]);

        VerumPresensi::create([
            'kelas_id' => $verum_kela->id,
            'judul_pertemuan' => $request->judul_pertemuan,
            'pertemuan_ke' => $request->pertemuan_ke,
            'waktu_buka' => now(),
            'waktu_tutup' => now()->addMinutes($request->durasi),
        ]);

        return back()->with('success', 'Sesi presensi berhasil dibuka.');
    }

    /**
     * Mahasiswa mencatatkan kehadiran.
     */
    public function storeKehadiran(Request $request, VerumPresensi $verum_presensi)
    {
        $mahasiswaId = Auth::user()->mahasiswa->id;

        // Cek apakah sesi masih dibuka
        if (now()->isAfter($verum_presensi->waktu_tutup)) {
            return back()->with('error', 'Sesi presensi sudah ditutup.');
        }

        // Cek apakah mahasiswa sudah absen
        $sudahAbsen = VerumKehadiran::where('presensi_id', $verum_presensi->id)
                                    ->where('mahasiswa_id', $mahasiswaId)
                                    ->exists();

        if ($sudahAbsen) {
            return back()->with('error', 'Anda sudah melakukan presensi untuk sesi ini.');
        }

        VerumKehadiran::create([
            'presensi_id' => $verum_presensi->id,
            'mahasiswa_id' => $mahasiswaId,
            'status' => 'hadir',
            'waktu_absen' => now(),
        ]);

        return back()->with('success', 'Kehadiran Anda berhasil dicatat.');
    }
}
