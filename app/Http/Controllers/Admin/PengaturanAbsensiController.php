<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanAbsensi;
use Illuminate\Http\Request;

class PengaturanAbsensiController extends Controller
{
    public function index()
    {
        // Ambil pengaturan pertama. 
        // Menggunakan firstOrCreate untuk mencegah error jika database masih kosong.
        $pengaturan = PengaturanAbsensi::firstOrCreate([], [
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
            'toleransi_terlambat_menit' => 15
        ]);
        
        return view('admin.absensi.pengaturan', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
            'toleransi_terlambat_menit' => 'required|integer|min:0',
        ]);

        $pengaturan = PengaturanAbsensi::first();
        
        $pengaturan->update([
            'jam_masuk' => $request->jam_masuk,
            'jam_pulang' => $request->jam_pulang,
            'toleransi_terlambat_menit' => $request->toleransi_terlambat_menit,
        ]);

        return redirect()->back()->with('success', 'Pengaturan absensi berhasil diperbarui.');
    }
}