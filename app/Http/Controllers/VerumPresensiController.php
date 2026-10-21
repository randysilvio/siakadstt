<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumPresensi;
use App\Models\VerumKehadiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerumPresensiController extends Controller
{
    private function isDosenPengampu($user, VerumKelas $kelas): bool
    {
        if (!$user || !$user->dosen) return false;
        if ((int) $user->dosen->id === (int) $kelas->dosen_id) return true;
        return DB::table('dosen_mata_kuliah')
            ->where('mata_kuliah_id', $kelas->mata_kuliah_id)
            ->where('dosen_id', $user->dosen->id)
            ->exists();
    }

    /**
     * Dosen membuka sesi presensi baru.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        if (!$this->isDosenPengampu(Auth::user(), $verum_kela)) {
            abort(403, 'Hanya dosen pengampu yang bisa membuka presensi.');
        }

        $request->validate([
            'judul_pertemuan' => 'required|string|max:255',
            'pertemuan_ke' => 'required|integer|min:1',
            'durasi' => 'required|integer|min:1',
        ]);

        $verum_kela->presensi()->create([
            'judul_pertemuan' => strip_tags($request->judul_pertemuan),
            'pertemuan_ke' => $request->pertemuan_ke,
            'waktu_buka' => now(),
            'waktu_tutup' => now()->addMinutes((int) $request->durasi),
        ]);

        return back()->with('success', 'Sesi presensi berhasil dibuka.');
    }

    /**
     * Mahasiswa mencatatkan kehadiran.
     */
    public function storeKehadiran(Request $request, VerumPresensi $verum_presensi): RedirectResponse
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa || $mahasiswa->status_krs !== 'Disetujui') {
            abort(403, 'Aksi ini hanya untuk mahasiswa dengan KRS yang telah disetujui.');
        }

        // [PERBAIKAN FASE 5]: Pastikan mahasiswa terdaftar di mata kuliah ini pada tahun akademik kelas tersebut
        $isEnrolled = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $verum_presensi->kelas->tahun_akademik_id)
            ->where('mata_kuliahs.id', $verum_presensi->kelas->mata_kuliah_id)
            ->exists();

        if (!$isEnrolled) {
            return back()->with('error', 'Anda tidak terdaftar di kelas ini pada semester berjalan.');
        }

        if (now()->isAfter($verum_presensi->waktu_tutup)) {
            return back()->with('error', 'Sesi presensi sudah ditutup.');
        }

        $sudahAbsen = VerumKehadiran::where('presensi_id', $verum_presensi->id)
                                    ->where('mahasiswa_id', $mahasiswa->id)
                                    ->exists();

        if ($sudahAbsen) {
            return back()->with('warning', 'Anda sudah melakukan presensi untuk sesi ini.');
        }

        VerumKehadiran::create([
            'presensi_id' => $verum_presensi->id,
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'hadir',
            'waktu_absen' => now(),
        ]);

        return back()->with('success', 'Kehadiran Anda berhasil dicatat.');
    }

    /**
     * Menampilkan dan menyiapkan data halaman Cetak Rekap Absensi untuk Dosen.
     */
    public function cetakRekap(VerumKelas $verum_kela): View
    {
        if (!$this->isDosenPengampu(Auth::user(), $verum_kela)) {
            abort(403, 'Hanya dosen pengampu yang bisa mencetak absensi.');
        }

        $verum_kela->load(['mataKuliah', 'dosen', 'tahunAkademik']);

        // [PERBAIKAN BUG FASE 5]: Hanya ambil mahasiswa pada Tahun Akademik kelas ini yang KRS-nya Disetujui
        $mahasiswas = $verum_kela->mataKuliah->mahasiswas()
            ->wherePivot('tahun_akademik_id', $verum_kela->tahun_akademik_id)
            ->where('mahasiswas.status_krs', 'Disetujui')
            ->orderBy('nama_lengkap')
            ->get();
        
        $presensis = $verum_kela->presensi()->with('kehadiran')->orderBy('pertemuan_ke')->get();

        return view('verum.presensi_cetak', compact('verum_kela', 'mahasiswas', 'presensis'));
    }
}