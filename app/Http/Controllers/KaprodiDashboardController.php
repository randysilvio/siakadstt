<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgramStudi;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\KrsPersetujuan;
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
        // Satu dosen dapat menjabat Kaprodi di lebih dari satu prodi: pilih lewat parameter ?prodi=
        $daftarProdiKaprodi = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->orderBy('nama_prodi')->get();
        $programStudi = $daftarProdiKaprodi->firstWhere('id', (int) $request->input('prodi')) ?? $daftarProdiKaprodi->first();

        if (!$programStudi) {
            abort(403, 'Akses ditolak. Akun dosen Anda belum ditautkan sebagai Ketua Program Studi pada data Master Program Studi. Silakan hubungi Administrator.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->first();
        $periodeId = $periodeAktif->id ?? 0;

        $basisAktif = fn () => Mahasiswa::where('program_studi_id', $programStudi->id)->where('status_mahasiswa', 'Aktif');
        $hitungStatus = fn (string $status) => $basisAktif()
            ->whereHas('krsPersetujuans', fn ($q) => $q->where('tahun_akademik_id', $periodeId)->where('status', $status))
            ->whereHas('mataKuliahs', fn ($q) => $q->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeId))
            ->count();

        $countSemua     = $basisAktif()->count();
        $countMenunggu  = $hitungStatus(KrsPersetujuan::MENUNGGU);
        $countDisetujui = $hitungStatus(KrsPersetujuan::DISETUJUI);
        $countDitolak   = $hitungStatus(KrsPersetujuan::DITOLAK);
        $countBelum = max(0, $countSemua - ($countMenunggu + $countDisetujui + $countDitolak));

        // Early Warning System (EWS): mahasiswa aktif yang SUDAH memiliki nilai dan IPK < 2.00
        // (termasuk IPK 0,00 karena seluruh nilainya E — kelompok paling rawan DO).
        $semuaMhsAktifProdi = Mahasiswa::with(['dosenWali'])
            ->where('program_studi_id', $programStudi->id)
            ->where('status_mahasiswa', 'Aktif')
            ->whereHas('mataKuliahs', fn ($q) => $q->whereNotNull('mahasiswa_mata_kuliah.nilai'))
            ->get();

        $mahasiswaRawanDo = $semuaMhsAktifProdi->filter(function ($mhs) {
            $ipk = $mhs->hitungIpk();
            $mhs->ipk_hitung = $ipk;
            $mhs->sks_lulus_hitung = $mhs->totalSksLulus();
            return $ipk < 2.00;
        })->values();

        // Query Tabel Utama
        $query = $programStudi->mahasiswas()->with(['user', 'dosenWali', 'mataKuliahs'])->where('status_mahasiswa', 'Aktif');

        $query->when($request->filled('status'), function ($q) use ($request, $periodeId) {
            if ($request->status == 'Belum') {
                $q->whereDoesntHave('mataKuliahs', function ($sub) use ($periodeId) {
                    $sub->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeId);
                });
            } else {
                $q->whereHas('krsPersetujuans', fn ($sub) => $sub->where('tahun_akademik_id', $periodeId)->where('status', $request->status))
                  ->whereHas('mataKuliahs', function ($sub) use ($periodeId) {
                      $sub->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeId);
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
            $mhs->status_krs_display = $hasKrsThisSemester
                ? $mhs->statusKrsPada($periodeAktif->id)
                : 'Belum Mengajukan';
            $mhs->ipk_hitung = $mhs->hitungIpk();
            $mhs->sks_lulus_hitung = $mhs->totalSksLulus();
        }

        return view('kaprodi.dashboard', compact(
            'programStudi', 'daftarProdiKaprodi', 'mahasiswas', 'mahasiswaRawanDo',
            'countSemua', 'countMenunggu', 'countDisetujui', 'countDitolak', 'countBelum'
        ));
    }
}