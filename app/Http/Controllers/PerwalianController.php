<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Models\Dosen;
use App\Models\KrsPersetujuan;
use App\Services\AcademicRuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerwalianController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->dosen) {
            abort(403, 'Data dosen tidak ditemukan. Pastikan profil dosen Anda sudah lengkap.');
        }
        $dosen = $user->dosen;
        
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        $mahasiswa_wali = Mahasiswa::where('dosen_wali_id', $dosen->id)
                            ->where('status_mahasiswa', 'Aktif')
                            ->with(['programStudi', 'mataKuliahs'])
                            ->orderBy('nama_lengkap')
                            ->get();

        foreach ($mahasiswa_wali as $mhs) {
            $hasKrsThisSemester = false;
            if ($periodeAktif) {
                $hasKrsThisSemester = $mhs->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
            }

            $mhs->status_krs_display = ($hasKrsThisSemester && $periodeAktif)
                ? $mhs->statusKrsPada($periodeAktif->id)
                : 'Belum Mengajukan';
            $mhs->ipk_hitung = $mhs->hitungIpk();
        }

        // Penetapan dosen wali hanya oleh Kaprodi (untuk prodinya) atau Admin (menu Data Mahasiswa).
        $prodiKaprodiIds = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->pluck('id')->all();
        $bisaMenetapkan = !empty($prodiKaprodiIds);

        $query = Mahasiswa::whereNull('dosen_wali_id')
                    ->where('status_mahasiswa', 'Aktif')
                    ->whereIn('program_studi_id', $prodiKaprodiIds)
                    ->with('programStudi')
                    ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        if ($request->filled('program_studi_id')) {
            $query->where('program_studi_id', $request->input('program_studi_id'));
        }

        if ($request->filled('angkatan')) {
            $query->where('tahun_masuk', $request->input('angkatan'));
        }

        $mahasiswa_tersedia = $query->paginate(10)->withQueryString();
        $program_studis = ProgramStudi::whereIn('id', $prodiKaprodiIds)->orderBy('nama_prodi')->get();
        $angkatans = Mahasiswa::whereNull('dosen_wali_id')
                        ->where('status_mahasiswa', 'Aktif')
                        ->whereIn('program_studi_id', $prodiKaprodiIds)
                        ->select('tahun_masuk')->distinct()->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk');
        $dosenPilihan = $bisaMenetapkan
            ? Dosen::where(function ($q) use ($prodiKaprodiIds) {
                    $q->whereIn('program_studi_id', $prodiKaprodiIds)->orWhereNull('program_studi_id');
                })->orderBy('nama_lengkap')->get(['id', 'nama_lengkap'])
            : collect();

        return view('perwalian.index', compact('mahasiswa_wali', 'mahasiswa_tersedia', 'program_studis', 'angkatans', 'dosen', 'bisaMenetapkan', 'prodiKaprodiIds', 'dosenPilihan'));
    }

    public function show($id): View
    {
        $user = Auth::user();
        if (!$user->dosen) abort(403);
        $dosen = $user->dosen;
        
        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        $mahasiswa = Mahasiswa::where('id', $id)
                        ->where('dosen_wali_id', $dosen->id)
                        ->firstOrFail();

        $krs = $mahasiswa->mataKuliahs()
                         ->wherePivot('tahun_akademik_id', $periodeAktif->id)
                         ->with('jadwals')
                         ->get();

        // Jika di semester aktif ini mahasiswa belum punya mata kuliah sama sekali, pastikan status_krs tidak nyangkut di Disetujui
        if ($krs->isEmpty() && $mahasiswa->statusKrsPada($periodeAktif->id) !== KrsPersetujuan::BELUM) {
            $mahasiswa->setStatusKrs($periodeAktif->id, KrsPersetujuan::BELUM);
        }
                         
        $totalSks = $krs->sum('sks');
        $ipk = $mahasiswa->hitungIpk();
        $sksLulus = $mahasiswa->totalSksLulus();

        $bimbinganIds = Mahasiswa::where('dosen_wali_id', $dosen->id)
            ->where('status_mahasiswa', 'Aktif')
            ->orderBy('nama_lengkap')
            ->pluck('id')
            ->toArray();

        $prevMahasiswaId = null;
        $nextMahasiswaId = null;
        $currentIndex = array_search($mahasiswa->id, $bimbinganIds);
        if ($currentIndex !== false) {
            $prevMahasiswaId = $bimbinganIds[$currentIndex - 1] ?? null;
            $nextMahasiswaId = $bimbinganIds[$currentIndex + 1] ?? null;
        }

        return view('perwalian.show', compact(
            'mahasiswa',
            'krs',
            'totalSks',
            'periodeAktif',
            'ipk',
            'sksLulus',
            'prevMahasiswaId',
            'nextMahasiswaId'
        ));
    }

    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->dosen) abort(403);
        $dosen = $user->dosen;

        $mahasiswa = Mahasiswa::where('id', $id)->where('dosen_wali_id', $dosen->id)->firstOrFail();

        $request->validate([
            'status_krs' => 'required|in:Disetujui,Ditolak,Menunggu Persetujuan',
            'catatan_kaprodi' => 'nullable|string|max:1000',
        ]);

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();
        $error = app(AcademicRuleService::class)->terapkanValidasiKrs($mahasiswa, $periodeAktif, $request->status_krs, $request->input('catatan_kaprodi'), $user);
        if ($error) {
            return redirect()->back()->with('error', $error);
        }

        $pesan = $request->status_krs == 'Disetujui'
            ? 'KRS Mahasiswa ' . $mahasiswa->nama_lengkap . ' berhasil DISETUJUI.'
            : 'Status KRS mahasiswa berhasil diperbarui menjadi ' . strtoupper($request->status_krs) . '.';

        return redirect()->back()->with('success', $pesan);
    }

    /**
     * Penetapan dosen wali oleh Kaprodi untuk mahasiswa prodinya yang belum memiliki dosen wali.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mahasiswa_ids' => 'required|array',
            'mahasiswa_ids.*' => 'exists:mahasiswas,id',
            'dosen_wali_id' => 'nullable|exists:dosens,id',
        ]);

        $user = Auth::user();
        if (!$user->dosen) abort(403);

        $prodiKaprodiIds = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->pluck('id')->all();
        if (empty($prodiKaprodiIds)) {
            return redirect()->route('perwalian.index')->with('error', 'Penetapan dosen wali hanya dapat dilakukan oleh Kaprodi atau Administrator.');
        }

        $dosenWaliId = (int) ($request->input('dosen_wali_id') ?: $user->dosen->id);

        $jumlah = Mahasiswa::whereIn('id', $request->mahasiswa_ids)
                     ->where('status_mahasiswa', 'Aktif')
                     ->whereNull('dosen_wali_id')
                     ->whereIn('program_studi_id', $prodiKaprodiIds)
                     ->update(['dosen_wali_id' => $dosenWaliId]);

        $namaDosen = Dosen::whereKey($dosenWaliId)->value('nama_lengkap');

        return redirect()->route('perwalian.index')->with('success', "{$jumlah} mahasiswa berhasil ditetapkan ke dosen wali {$namaDosen}.");
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->dosen) abort(403);
        $dosen = $user->dosen;

        $isKaprodiProdi = ProgramStudi::where('id', $mahasiswa->program_studi_id)
            ->where('kaprodi_dosen_id', $dosen->id)
            ->exists();

        if ($isKaprodiProdi) {
            $mahasiswa->update(['dosen_wali_id' => null]);
            return redirect()->route('perwalian.index')->with('success', 'Mahasiswa dilepas dari dosen wali.');
        }

        return redirect()->route('perwalian.index')->with('error', 'Pelepasan dosen wali hanya dapat dilakukan oleh Kaprodi program studi mahasiswa atau Administrator.');
    }

    public function destroyKrs($mahasiswa_id, $mk_id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->dosen) abort(403);
        $dosen = $user->dosen;
        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();

        $mahasiswa = Mahasiswa::where('id', $mahasiswa_id)
                        ->where('dosen_wali_id', $dosen->id)
                        ->firstOrFail();

        if ($mahasiswa->krsDisetujuiPada($periodeAktif->id)) {
            return redirect()->back()->with('error', 'KRS sudah disetujui. Buka kembali persetujuan terlebih dahulu sebelum mengubah mata kuliah.');
        }

        $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->detach($mk_id);

        // Jika seluruh mata kuliah di semester ini habis dihapus, kembalikan status ke 'Belum Mengajukan'
        $masihAda = $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->exists();
        if (!$masihAda) {
            $mahasiswa->setStatusKrs($periodeAktif->id, KrsPersetujuan::BELUM);
        }

        return redirect()->back()->with('success', 'Mata kuliah berhasil dihapus dari KRS mahasiswa.');
    }
}