<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
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

        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->firstOrFail();

        if ((int) $mahasiswa->program_studi_id !== (int) $programStudi->id) {
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
            ->where('status_krs', 'Menunggu Persetujuan')
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

        $programStudi = ProgramStudi::where('kaprodi_dosen_id', $user->dosen->id)->firstOrFail();

        if ((int) $mahasiswa->program_studi_id !== (int) $programStudi->id) {
            abort(403, 'Akses ditolak. Mahasiswa ini bukan berasal dari program studi Anda.');
        }

        $catatan = $request->filled('catatan_kaprodi') ? strip_tags($request->catatan_kaprodi) : null;
        if ($request->status_krs === 'Disetujui' && !$request->filled('catatan_kaprodi')) {
            $catatan = null;
        }

        $mahasiswa->update([
            'status_krs' => $request->status_krs,
            'catatan_kaprodi' => $catatan,
        ]);

        if ($mahasiswa->user && in_array($request->status_krs, ['Disetujui', 'Ditolak'])) {
            $mahasiswa->user->notify(new KrsValidated($mahasiswa, $request->status_krs));
        }

        // [PERBAIKAN UX STAY-ON-PAGE]: Tetap bertahan di halaman detail KRS mahasiswa ini agar tidak banyak klik
        return redirect()->back()->with('success', 'Status KRS mahasiswa ' . $mahasiswa->nama_lengkap . ' berhasil diperbarui menjadi ' . strtoupper($request->status_krs) . '.');
    }
}