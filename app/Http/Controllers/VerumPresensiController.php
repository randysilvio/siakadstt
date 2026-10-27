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
     * Dosen membuka sesi presensi baru sekaligus mengisi Jurnal / BAP Pertemuan.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        if (!$this->isDosenPengampu(Auth::user(), $verum_kela)) {
            abort(403, 'Hanya dosen pengampu yang bisa membuka presensi.');
        }

        $request->validate([
            'judul_pertemuan'     => 'required|string|max:255',
            'materi_aktual'       => 'nullable|string|max:1500',
            'metode_pembelajaran' => 'nullable|string|max:100',
            'pertemuan_ke'        => 'required|integer|min:1|max:32',
            'durasi'              => 'required|integer|min:1',
        ]);

        $verum_kela->presensi()->create([
            'judul_pertemuan'     => strip_tags($request->judul_pertemuan),
            'materi_aktual'       => $request->filled('materi_aktual') ? strip_tags($request->materi_aktual) : null,
            'metode_pembelajaran' => $request->filled('metode_pembelajaran') ? strip_tags($request->metode_pembelajaran) : 'Tatap Muka / Ceramah & Diskusi',
            'pertemuan_ke'        => $request->pertemuan_ke,
            'waktu_buka'          => now(),
            'waktu_tutup'         => now()->addMinutes((int) $request->durasi),
        ]);

        return back()->with('success', 'Sesi presensi dan Jurnal BAP Pertemuan ke-' . $request->pertemuan_ke . ' berhasil dibuka.');
    }

    /**
     * Dosen memperbarui Jurnal / BAP Pertemuan (Stay-on-Page).
     */
    public function updateJurnal(Request $request, VerumPresensi $verum_presensi): RedirectResponse
    {
        if (!$verum_presensi->kelas || !$this->isDosenPengampu(Auth::user(), $verum_presensi->kelas)) {
            abort(403, 'Hanya dosen pengampu yang dapat mengubah Jurnal BAP.');
        }

        $request->validate([
            'judul_pertemuan'     => 'required|string|max:255',
            'materi_aktual'       => 'nullable|string|max:1500',
            'metode_pembelajaran' => 'nullable|string|max:100',
        ]);

        $verum_presensi->update([
            'judul_pertemuan'     => strip_tags($request->judul_pertemuan),
            'materi_aktual'       => $request->filled('materi_aktual') ? strip_tags($request->materi_aktual) : null,
            'metode_pembelajaran' => $request->filled('metode_pembelajaran') ? strip_tags($request->metode_pembelajaran) : 'Tatap Muka / Ceramah & Diskusi',
        ]);

        return back()->with('success', 'Jurnal Berita Acara Perkuliahan (Pertemuan ke-' . $verum_presensi->pertemuan_ke . ') berhasil diperbarui.');
    }

    /**
     * Mahasiswa mencatatkan kehadiran.
     */
    public function storeKehadiran(Request $request, VerumPresensi $verum_presensi): RedirectResponse
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa || !$verum_presensi->kelas) {
            abort(403, 'Aksi ini hanya untuk mahasiswa peserta kelas.');
        }

        // Peserta resmi: KRS disetujui pada semester kelas & rombel sesuai rombel kelas
        if (!$verum_presensi->kelas->isPeserta($mahasiswa)) {
            return back()->with('error', 'Anda tidak terdaftar sebagai peserta kelas/rombel ini.');
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
            'presensi_id'  => $verum_presensi->id,
            'mahasiswa_id' => $mahasiswa->id,
            'status'       => 'hadir',
            'waktu_absen'  => now(),
        ]);

        return back()->with('success', 'Kehadiran Anda berhasil dicatat.');
    }

    /**
     * Menampilkan halaman Cetak Rekap Matriks Absensi Mahasiswa.
     */
    public function cetakRekap(VerumKelas $verum_kela): View
    {
        if (!$this->isDosenPengampu(Auth::user(), $verum_kela)) {
            abort(403, 'Hanya dosen pengampu yang bisa mencetak absensi.');
        }

        $verum_kela->load(['mataKuliah', 'dosen', 'tahunAkademik']);

        $mahasiswas = $verum_kela->pesertaQuery()
            ->orderBy('nama_lengkap')
            ->get();
        
        $presensis = $verum_kela->presensi()->with('kehadiran')->orderBy('pertemuan_ke')->get();

        return view('verum.presensi_cetak', compact('verum_kela', 'mahasiswas', 'presensis'));
    }

    /**
     * [FITUR BARU FASE 6]: Cetak Berita Acara Perkuliahan (BAP) & Jurnal Mengajar Dosen (16 Pertemuan).
     */
    public function cetakBap(VerumKelas $verum_kela): View
    {
        if (!$this->isDosenPengampu(Auth::user(), $verum_kela)) {
            abort(403, 'Hanya dosen pengampu yang bisa mencetak BAP.');
        }

        $verum_kela->load(['mataKuliah.kurikulum.programStudi', 'dosen', 'tahunAkademik']);

        $totalPeserta = $verum_kela->pesertaQuery()->count();

        $presensis = $verum_kela->presensi()->with('kehadiran')->orderBy('pertemuan_ke')->get();

        return view('verum.bap_cetak', compact('verum_kela', 'totalPeserta', 'presensis'));
    }
}