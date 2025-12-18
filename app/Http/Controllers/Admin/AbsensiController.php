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
     * [BARU] Mencetak Laporan Absensi (Print View)
     */
    public function laporanCetak(Request $request)
    {
        $query = AbsensiPegawai::with('user')->latest();

        // 1. Terapkan Filter Nama (Sama seperti Index)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // 2. Terapkan Filter Tanggal (Sama seperti Index)
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_absensi', $request->tanggal);
        }

        // Ambil semua data tanpa pagination untuk dicetak
        $laporan = $query->get(); 
        
        // Kirim info filter untuk judul laporan
        $filterInfo = [];
        if($request->filled('tanggal')) $filterInfo[] = 'Tanggal: ' . \Carbon\Carbon::parse($request->tanggal)->translatedFormat('d F Y');
        if($request->filled('search')) $filterInfo[] = 'Pencarian: "' . $request->search . '"';

        // Load view khusus cetak
        return view('admin.absensi.laporan_cetak', compact('laporan', 'filterInfo'));
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