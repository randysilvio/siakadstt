<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Models\KrsPersetujuan;
use App\Services\AcademicRuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Notifications\KrsValidated;

class ValidasiKrsController extends Controller
{
    /**
     * Menampilkan detail KRS seorang mahasiswa untuk divalidasi oleh Kaprodi.
     */
    public function show(Mahasiswa $mahasiswa): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !$user->dosen || (!$user->hasRole('kaprodi') && !$user->isKaprodi())) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        // Seorang dosen dapat menjabat Kaprodi di lebih dari satu prodi; cek prodi milik mahasiswa ini.
        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)
            ->where('id', $mahasiswa->program_studi_id)
            ->first();

        if (!$programStudi) {
            abort(403, 'Anda hanya dapat memvalidasi mahasiswa dari program studi Anda.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();
        
        $mahasiswa->load([
            'dosenWali',
            'mataKuliahs' => function ($query) use ($periodeAktif) {
                $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeAktif->id)
                      ->with(['jadwals', 'dosen']);
            }
        ]);

        $ipk = $mahasiswa->hitungIpk();
        $sksLulus = $mahasiswa->totalSksLulus();

        // Cari mahasiswa berikutnya & sebelumnya yang berstatus Menunggu Persetujuan di Prodi yang sama (Zero-Click Navigation)
        $antreanIds = Mahasiswa::where('program_studi_id', $programStudi->id)
            ->where('status_mahasiswa', 'Aktif')
            ->whereHas('krsPersetujuans', fn($q) => $q->where('tahun_akademik_id', $periodeAktif->id)->where('status', KrsPersetujuan::MENUNGGU))
            ->whereHas('mataKuliahs', fn($q) => $q->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeAktif->id))
            ->orderBy('nim', 'asc')
            ->pluck('id')
            ->toArray();

        $nextMahasiswaId = null;
        $prevMahasiswaId = null;

        if (!empty($antreanIds)) {
            $currentIndex = array_search($mahasiswa->id, $antreanIds);
            if ($currentIndex !== false) {
                $prevMahasiswaId = $antreanIds[$currentIndex - 1] ?? null;
                $nextMahasiswaId = $antreanIds[$currentIndex + 1] ?? null;
            } else {
                // Jika mahasiswa saat ini baru saja selesai divalidasi, arahkan tombol "Berikutnya" ke antrean pertama yang tersisa
                $nextMahasiswaId = $antreanIds[0];
            }
        }
        
        return view('kaprodi.validasi_krs', compact(
            'mahasiswa',
            'periodeAktif',
            'ipk',
            'sksLulus',
            'prevMahasiswaId',
            'nextMahasiswaId'
        ));
    }

    /**
     * Memproses aksi validasi (setujui/tolak) dan tetap bertahan di halaman saat ini (Stay-on-Page).
     */
    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'status_krs' => 'required|in:Disetujui,Ditolak,Menunggu Persetujuan',
            'catatan_kaprodi' => 'nullable|string|max:1000',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !$user->dosen || (!$user->hasRole('kaprodi') && !$user->isKaprodi())) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $isKaprodiProdi = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)
            ->where('id', $mahasiswa->program_studi_id)
            ->exists();

        if (!$isKaprodiProdi) {
            abort(403, 'Akses ditolak. Mahasiswa ini bukan berasal dari program studi Anda.');
        }

        $periodeAktif = TahunAkademik::where('is_active', true)->firstOrFail();
        $error = app(AcademicRuleService::class)->terapkanValidasiKrs($mahasiswa, $periodeAktif, $request->status_krs, $request->input('catatan_kaprodi'), $user);
        if ($error) {
            return redirect()->back()->with('error', $error);
        }

        // [PERBAIKAN UX STAY-ON-PAGE]: Tetap bertahan di halaman detail KRS mahasiswa ini agar tidak banyak klik
        return redirect()->back()->with('success', 'Status KRS mahasiswa ' . $mahasiswa->nama_lengkap . ' berhasil diperbarui menjadi ' . strtoupper($request->status_krs) . '.');
    }
}
