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

        $countSemua = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_mahasiswa', 'Aktif')
                            ->count();
        
        $countMenunggu = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_mahasiswa', 'Aktif')
                            ->where('status_krs', 'Menunggu Persetujuan')
                            ->whereHas('mataKuliahs', function ($q) use ($periodeAktif) {
                                $q->where('tahun_akademik_id', $periodeAktif->id ?? null);
                            })
                            ->count();
                            
        $countDisetujui = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_mahasiswa', 'Aktif')
                            ->where('status_krs', 'Disetujui')
                            ->whereHas('mataKuliahs', function ($q) use ($periodeAktif) {
                                $q->where('tahun_akademik_id', $periodeAktif->id ?? null);
                            })
                            ->count();
                            
        $countDitolak = Mahasiswa::where('program_studi_id', $programStudi->id)
                            ->where('status_mahasiswa', 'Aktif')
                            ->where('status_krs', 'Ditolak')
                            ->whereHas('mataKuliahs', function ($q) use ($periodeAktif) {
                                $q->where('tahun_akademik_id', $periodeAktif->id ?? null);
                            })
                            ->count();
                            
        $countBelum = max(0, $countSemua - ($countMenunggu + $countDisetujui + $countDitolak));

        // Deteksi Early Warning System (EWS): Mahasiswa Aktif di Prodi ini yang memiliki IPK > 0 dan < 2.00
        $semuaMhsAktifProdi = Mahasiswa::with(['mataKuliahs', 'dosenWali'])
            ->where('program_studi_id', $programStudi->id)
            ->where('status_mahasiswa', 'Aktif')
            ->get();

        $mahasiswaRawanDo = $semuaMhsAktifProdi->filter(function ($mhs) {
            $ipk = $mhs->hitungIpk();
            $mhs->ipk_hitung = $ipk;
            $mhs->sks_lulus_hitung = $mhs->totalSksLulus();
            return $ipk > 0 && $ipk < 2.00;
        })->values();

        // Query Tabel Utama
        $query = $programStudi->mahasiswas()->with(['user', 'dosenWali', 'mataKuliahs'])->where('status_mahasiswa', 'Aktif');

        $query->when($request->filled('status'), function ($q) use ($request, $periodeAktif) {
            if ($request->status == 'Belum') {
                $q->whereDoesntHave('mataKuliahs', function ($sub) use ($periodeAktif) {
                    $sub->where('tahun_akademik_id', $periodeAktif->id ?? null);
                });
            } else {
                $q->where('status_krs', $request->status)
                  ->whereHas('mataKuliahs', function ($sub) use ($periodeAktif) {
                      $sub->where('tahun_akademik_id', $periodeAktif->id ?? null);
                  });
            }
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nim', 'like', "%{$search}%");
            });
        });

        $mahasiswas = $query->orderBy('nim', 'asc')->paginate(10)->withQueryString();

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
            $mhs->ipk_hitung = $mhs->hitungIpk();
            $mhs->sks_lulus_hitung = $mhs->totalSksLulus();
        }

        return view('kaprodi.dashboard', compact(
            'programStudi', 'mahasiswas', 'mahasiswaRawanDo',
            'countSemua', 'countMenunggu', 'countDisetujui', 'countDitolak', 'countBelum'
        ));
    }
}