<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPegawai;
use App\Models\LokasiKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Menampilkan halaman laporan absensi (Web View).
     * Menampilkan detail harian dengan filter Bulan & Tahun.
     */
    public function laporanIndex(Request $request)
    {
        // 1. Ambil Filter (Default: Bulan & Tahun Saat Ini)
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $query = AbsensiPegawai::with('user')->latest();

        // 2. Filter Search Nama
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // 3. Filter Bulan & Tahun
        $query->whereMonth('tanggal_absensi', $bulan)
              ->whereYear('tanggal_absensi', $tahun);

        $laporan = $query->paginate(20)->withQueryString();

        return view('admin.absensi.laporan', compact('laporan'));
    }

    /**
     * [REKAPITULASI] Mencetak Laporan Bulanan Ringkas
     * Mengelompokkan data per pegawai (1 Orang = 1 Baris)
     */
    public function laporanCetak(Request $request)
    {
        // 1. Ambil Filter (Default: Bulan & Tahun Saat Ini)
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        // Info untuk Judul Laporan
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $filterInfo[] = "Periode: " . $namaBulan;

        if ($request->filled('search')) {
            $filterInfo[] = 'Pencarian: "' . $request->search . '"';
        }

        // 2. Ambil User yang memiliki data absensi pada bulan tersebut
        // Kita gunakan User sebagai model utama agar bisa menghitung total status
        $laporan = User::whereHas('absensiPegawai', function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal_absensi', $bulan)
                  ->whereYear('tanggal_absensi', $tahun);
            })
            ->with(['absensiPegawai' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal_absensi', $bulan)
                  ->whereYear('tanggal_absensi', $tahun);
            }])
            ->when($request->filled('search'), function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            })
            ->get()
            ->map(function($user) {
                // 3. Hitung Statistik Per Orang
                return [
                    'nama' => $user->name,
                    'total_hadir' => $user->absensiPegawai->where('status_kehadiran', 'Hadir')->count(),
                    'total_izin' => $user->absensiPegawai->where('status_kehadiran', 'Izin')->count(),
                    'total_sakit' => $user->absensiPegawai->where('status_kehadiran', 'Sakit')->count(),
                    'total_alpha' => $user->absensiPegawai->where('status_kehadiran', 'Alpha')->count(),
                    // Hitung Terlambat (Contoh: Lewat jam 08:00)
                    'total_terlambat' => $user->absensiPegawai->filter(function($item) {
                        return $item->waktu_check_in && $item->waktu_check_in->format('H:i') > '08:00'; 
                    })->count(),
                ];
            });

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

    public function lokasiCreate()
    {
        return view('admin.absensi.lokasi_create');
    }

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

    public function lokasiEdit(LokasiKerja $lokasi)
    {
        return view('admin.absensi.lokasi_edit', compact('lokasi'));
    }

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

    public function lokasiDestroy(LokasiKerja $lokasi)
    {
        $lokasi->delete();
        return redirect()->route('admin.absensi.lokasi.index')->with('success', 'Lokasi kerja berhasil dihapus.');
    }
}