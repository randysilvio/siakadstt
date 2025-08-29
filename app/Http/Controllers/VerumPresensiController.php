<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumPresensi;
use App\Models\VerumKehadiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class VerumPresensiController extends Controller
{
    /**
     * Dosen membuka sesi presensi baru.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        $this->authorize('update', $verum_kela);

        $request->validate([
            'judul_pertemuan' => 'required|string|max:255',
            'pertemuan_ke' => 'required|integer|min:1',
            'durasi' => 'required|integer|min:1',
        ]);

        $verum_kela->presensi()->create([
            'judul_pertemuan' => $request->judul_pertemuan,
            'pertemuan_ke' => $request->pertemuan_ke,
            'waktu_buka' => now(),
            'waktu_tutup' => now()->addMinutes((int)$request->durasi),
        ]);

        return back()->with('success', 'Sesi presensi berhasil dibuka.');
    }

    /**
     * Mahasiswa mencatatkan kehadiran.
     */
    public function storeKehadiran(Request $request, VerumPresensi $verum_presensi): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            abort(403, 'Aksi ini hanya untuk mahasiswa.');
        }

        if (now()->isAfter($verum_presensi->waktu_tutup)) {
            return back()->with('error', 'Sesi presensi sudah ditutup.');
        }

        $sudahAbsen = VerumKehadiran::where('presensi_id', $verum_presensi->id)
                                    ->where('mahasiswa_id', $mahasiswa->id)
                                    ->exists();

        if ($sudahAbsen) {
            return back()->with('warning', 'Anda sudah melakukan presensi untuk sesi ini.');
        }

        VerumKehadiran::create([
            'presensi_id' => $verum_presensi->id,
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'hadir',
            'waktu_absen' => now(),
        ]);

        return back()->with('success', 'Kehadiran Anda berhasil dicatat.');
    }
}