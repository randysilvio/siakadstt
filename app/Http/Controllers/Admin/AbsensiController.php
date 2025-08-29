<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPegawai;
use App\Models\LokasiKerja;
use App\Models\User;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    /**
     * Menampilkan halaman laporan absensi.
     */
    public function laporanIndex(Request $request)
    {
        $query = AbsensiPegawai::with('user')->latest();

        // Filter berdasarkan nama pegawai
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_absensi', $request->tanggal);
        }

        $laporan = $query->paginate(20)->withQueryString();

        return view('admin.absensi.laporan', compact('laporan'));
    }

    /**
     * Menampilkan halaman manajemen lokasi kerja.
     */
    public function lokasiIndex()
    {
        $lokasi = LokasiKerja::all();
        return view('admin.absensi.lokasi_index', compact('lokasi'));
    }

    /**
     * Menampilkan form untuk membuat lokasi kerja baru.
     */
    public function lokasiCreate()
    {
        return view('admin.absensi.lokasi_create');
    }

    /**
     * Menyimpan lokasi kerja baru.
     */
    public function lokasiStore(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_toleransi_meter' => 'required|integer|min:1',
        ]);

        LokasiKerja::create($request->all());

        return redirect()->route('admin.absensi.lokasi.index')->with('success', 'Lokasi kerja berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit lokasi kerja.
     */
    public function lokasiEdit(LokasiKerja $lokasi)
    {
        return view('admin.absensi.lokasi_edit', compact('lokasi'));
    }

    /**
     * Memperbarui data lokasi kerja.
     */
    public function lokasiUpdate(Request $request, LokasiKerja $lokasi)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_toleransi_meter' => 'required|integer|min:1',
        ]);

        $lokasi->update($request->all());

        return redirect()->route('admin.absensi.lokasi.index')->with('success', 'Lokasi kerja berhasil diperbarui.');
    }

    /**
     * Menghapus data lokasi kerja.
     */
    public function lokasiDestroy(LokasiKerja $lokasi)
    {
        $lokasi->delete();
        return redirect()->route('admin.absensi.lokasi.index')->with('success', 'Lokasi kerja berhasil dihapus.');
    }
}