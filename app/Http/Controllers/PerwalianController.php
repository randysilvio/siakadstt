<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Notifications\KrsValidated;
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
            
            if (!$hasKrsThisSemester) {
                $mhs->status_krs_display = 'Belum Mengajukan';
            } else {
                $mhs->status_krs_display = $mhs->status_krs;
            }
            $mhs->ipk_hitung = $mhs->hitungIpk();
        }

        $query = Mahasiswa::whereNull('dosen_wali_id')
                    ->where('status_mahasiswa', 'Aktif')
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
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        $angkatans = Mahasiswa::whereNull('dosen_wali_id')
                        ->where('status_mahasiswa', 'Aktif')
                        ->select('tahun_masuk')->distinct()->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk');

        return view('perwalian.index', compact('mahasiswa_wali', 'mahasiswa_tersedia', 'program_studis', 'angkatans', 'dosen'));
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
                         
        $totalSks = $krs->sum('sks');
        $ipk = $mahasiswa->hitungIpk();
        $sksLulus = $mahasiswa->totalSksLulus();

        // Navigasi Cepat Prev/Next Mahasiswa Bimbingan Tutorship
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

        $mahasiswa->status_krs = $request->status_krs;
        if ($request->filled('catatan_kaprodi')) {
            $mahasiswa->catatan_kaprodi = strip_tags($request->catatan_kaprodi);
        } elseif ($request->status_krs === 'Disetujui') {
            $mahasiswa->catatan_kaprodi = null;
        }
        $mahasiswa->save();

        if ($mahasiswa->user && in_array($request->status_krs, ['Disetujui', 'Ditolak'])) {
            $mahasiswa->user->notify(new KrsValidated($mahasiswa, $request->status_krs));
        }

        $pesan = $request->status_krs == 'Disetujui'
            ? 'KRS Mahasiswa ' . $mahasiswa->nama_lengkap . ' berhasil DISETUJUI oleh Dosen Tutor.'
            : 'Status KRS mahasiswa berhasil diperbarui menjadi ' . strtoupper($request->status_krs) . '.';

        return redirect()->back()->with('success', $pesan);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mahasiswa_ids' => 'required|array',
            'mahasiswa_ids.*' => 'exists:mahasiswas,id',
        ]);

        $user = Auth::user();
        if (!$user->dosen) abort(403);
        $dosen = $user->dosen;

        if ($request->has('mahasiswa_ids')) {
            Mahasiswa::whereIn('id', $request->mahasiswa_ids)
                     ->where('status_mahasiswa', 'Aktif')
                     ->whereNull('dosen_wali_id')
                     ->update(['dosen_wali_id' => $dosen->id]);
        }

        return redirect()->route('perwalian.index')->with('success', 'Mahasiswa berhasil ditambahkan ke daftar bimbingan Tutorship Anda.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->dosen) abort(403);
        $dosen = $user->dosen;

        if ((int) $mahasiswa->dosen_wali_id === (int) $dosen->id) {
            $mahasiswa->update(['dosen_wali_id' => null]);
            return redirect()->route('perwalian.index')->with('success', 'Mahasiswa dilepas dari bimbingan Tutorship.');
        }

        return redirect()->route('perwalian.index')->with('error', 'Akses ditolak.');
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

        $mahasiswa->mataKuliahs()->wherePivot('tahun_akademik_id', $periodeAktif->id)->detach($mk_id);

        return redirect()->back()->with('success', 'Mata kuliah berhasil dihapus (Koreksi Dosen Tutor).');
    }
}