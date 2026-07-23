<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgramStudi;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\View\View;

class KaprodiDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        if (!$user->dosen) {
            abort(403, 'Akses ditolak. Data dosen tidak ditemukan untuk pengguna ini.');
        }

        $dosen = $user->dosen;
        
        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->first(); 

        if (!$programStudi) {
            abort(403, 'Akses ditolak. Akun dosen Anda belum ditautkan sebagai Ketua Program Studi pada data Master Program Studi. Silakan hubungi Administrator.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        // --- MENGHITUNG DATA UNTUK SMART FILTER TABS (HANYA MAHASISWA AKTIF) ---
        // [PERBAIKAN] Penghitungan juga harus memperhatikan KRS pada semester aktif
        $countSemua = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_mahasiswa', 'Aktif')
                            ->count();
        
        $countMenunggu = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_mahasiswa', 'Aktif')
                            ->where('status_krs', 'Menunggu Persetujuan')
                            ->whereHas('mataKuliahs', function($q) use ($periodeAktif) {
                                $q->where('tahun_akademik_id', $periodeAktif->id ?? null);
                            })
                            ->count();
                            
        $countDisetujui = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_mahasiswa', 'Aktif')
                            ->where('status_krs', 'Disetujui')
                            ->whereHas('mataKuliahs', function($q) use ($periodeAktif) {
                                $q->where('tahun_akademik_id', $periodeAktif->id ?? null);
                            })
                            ->count();
                            
        $countDitolak = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_mahasiswa', 'Aktif')
                            ->where('status_krs', 'Ditolak')
                            ->whereHas('mataKuliahs', function($q) use ($periodeAktif) {
                                $q->where('tahun_akademik_id', $periodeAktif->id ?? null);
                            })
                            ->count();
                            
        $countBelum = $countSemua - ($countMenunggu + $countDisetujui + $countDitolak);

        // --- QUERY UTAMA DAFTAR MAHASISWA (TAMPILKAN YANG AKTIF SAJA) ---
        $query = $programStudi->mahasiswas()->with('user')->where('status_mahasiswa', 'Aktif');

        // [PERBAIKAN] Filter berdasarkan tombol status yang diklik, pastikan mengikatnya dengan semester aktif
        $query->when($request->filled('status'), function ($q) use ($request, $periodeAktif) {
            if ($request->status == 'Belum') {
                $q->whereDoesntHave('mataKuliahs', function($sub) use ($periodeAktif) {
                    $sub->where('tahun_akademik_id', $periodeAktif->id ?? null);
                });
            } else {
                $q->where('status_krs', $request->status)
                  ->whereHas('mataKuliahs', function($sub) use ($periodeAktif) {
                      $sub->where('tahun_akademik_id', $periodeAktif->id ?? null);
                  });
            }
        });

        // Filter pencarian ketikan
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nim', 'like', "%{$search}%");
            });
        });

        $mahasiswas = $query->paginate(10)->withQueryString();

        // Modifikasi display status secara on-the-fly untuk mahasiswa yang belum KRS semester ini tapi statusnya nyangkut
        foreach ($mahasiswas as $mhs) {
            $hasKrsThisSemester = false;
            if ($periodeAktif) {
                $hasKrsThisSemester = $mhs->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
            }
            if (!$hasKrsThisSemester) {
                $mhs->status_krs_display = 'Belum Mengajukan';
            } else {
                $mhs->status_krs_display = $mhs->status_krs;
            }
        }

        return view('kaprodi.dashboard', compact(
            'programStudi', 'mahasiswas', 
            'countSemua', 'countMenunggu', 'countDisetujui', 'countDitolak', 'countBelum'
        ));
    }
}