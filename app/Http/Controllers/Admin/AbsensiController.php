<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPegawai;
use App\Models\LokasiKerja;
use App\Models\PengaturanAbsensi;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Menampilkan halaman laporan absensi (Web View).
     */
    public function laporanIndex(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $query = AbsensiPegawai::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $query->whereMonth('tanggal_absensi', $bulan)
              ->whereYear('tanggal_absensi', $tahun);

        $laporan = $query->paginate(20)->withQueryString();

        return view('admin.absensi.laporan', compact('laporan'));
    }

    /**
     * [REKAPITULASI] Mencetak Laporan Bulanan Ringkas
     */
    public function laporanCetak(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        $filterInfo[] = "Periode: " . $namaBulan;

        if ($request->filled('search')) {
            $filterInfo[] = 'Pencarian: "' . $request->search . '"';
        }

        // Ambil pengaturan jam masuk & toleransi aktual dari database
        $setting = PengaturanAbsensi::first();
        $jamMasuk = $setting ? $setting->jam_masuk : '08:00:00';
        $toleransi = $setting ? (int) $setting->toleransi_terlambat_menit : 15;
        $batasTerlambat = Carbon::parse($jamMasuk)->addMinutes($toleransi)->format('H:i:s');

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
            ->map(function($user) use ($batasTerlambat) {
                return [
                    'nama' => $user->name,
                    'total_hadir' => $user->absensiPegawai->where('status_kehadiran', 'Hadir')->count(),
                    'total_izin' => $user->absensiPegawai->where('status_kehadiran', 'Izin')->count(),
                    'total_sakit' => $user->absensiPegawai->where('status_kehadiran', 'Sakit')->count(),
                    'total_alpha' => $user->absensiPegawai->where('status_kehadiran', 'Alpha')->count(),
                    'total_terlambat' => $user->absensiPegawai->filter(function($item) use ($batasTerlambat) {
                        return $item->waktu_check_in && $item->waktu_check_in->format('H:i:s') > $batasTerlambat; 
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
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_toleransi_meter' => 'required|integer|min:1',
        ]);

        LokasiKerja::create($validated);
        return redirect()->route('admin.absensi.lokasi.index')->with('success', 'Lokasi kerja berhasil ditambahkan.');
    }

    public function lokasiEdit(LokasiKerja $lokasi)
    {
        return view('admin.absensi.lokasi_edit', compact('lokasi'));
    }

    public function lokasiUpdate(Request $request, LokasiKerja $lokasi)
    {
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_toleransi_meter' => 'required|integer|min:1',
        ]);

        $lokasi->update($validated);
        return redirect()->route('admin.absensi.lokasi.index')->with('success', 'Lokasi kerja berhasil diperbarui.');
    }

    public function lokasiDestroy(LokasiKerja $lokasi)
    {
        // Riwayat absensi menunjuk lokasi ini (FK cascade): menghapusnya akan menghapus rekap kehadiran pegawai
        if (AbsensiPegawai::where('lokasi_kerja_id', $lokasi->id)->exists()) {
            return redirect()->route('admin.absensi.lokasi.index')
                ->with('error', 'Lokasi ini sudah memiliki riwayat absensi pegawai sehingga tidak dapat dihapus. Ubah koordinat/radiusnya bila lokasi berpindah.');
        }

        $lokasi->delete();
        return redirect()->route('admin.absensi.lokasi.index')->with('success', 'Lokasi kerja berhasil dihapus.');
    }
}