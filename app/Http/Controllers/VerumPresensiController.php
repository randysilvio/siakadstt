<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

use App\Models\VerumKelas;
use App\Models\VerumPresensi;
use App\Models\VerumKehadiran;
use App\Services\VerumService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerumPresensiController extends Controller
{
    /**
     * Dosen membuka sesi presensi baru sekaligus mengisi Jurnal / BAP Pertemuan.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            abort(403, 'Hanya dosen pengampu yang bisa membuka presensi.');
        }

        app(VerumService::class)->bukaPresensi($verum_kela, $request->validate(VerumService::ATURAN_PRESENSI));

        return back()->with('success', 'Sesi presensi dan Jurnal BAP Pertemuan ke-' . $request->pertemuan_ke . ' berhasil dibuka.');
    }

    /**
     * Dosen memperbarui Jurnal / BAP Pertemuan (Stay-on-Page).
     */
    public function updateJurnal(Request $request, VerumPresensi $verum_presensi): RedirectResponse
    {
        if (!$verum_presensi->kelas || !Gate::forUser(Auth::user())->allows('kelola', $verum_presensi->kelas)) {
            abort(403, 'Hanya dosen pengampu yang dapat mengubah Jurnal BAP.');
        }

        app(VerumService::class)->perbaruiJurnal($verum_presensi, $request->validate(VerumService::ATURAN_JURNAL));

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

        [$jenis, $pesan] = app(VerumService::class)->hadir($verum_presensi, $mahasiswa);

        return back()->with($jenis, $pesan);
    }

    /**
     * Menampilkan halaman Cetak Rekap Matriks Absensi Mahasiswa.
     */
    public function cetakRekap(VerumKelas $verum_kela): View
    {
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
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
        if (!Gate::forUser(Auth::user())->allows('kelola', $verum_kela)) {
            abort(403, 'Hanya dosen pengampu yang bisa mencetak BAP.');
        }

        $verum_kela->load(['mataKuliah.kurikulum.programStudi', 'dosen', 'tahunAkademik']);

        $totalPeserta = $verum_kela->pesertaQuery()->count();

        $presensis = $verum_kela->presensi()->with('kehadiran')->orderBy('pertemuan_ke')->get();

        return view('verum.bap_cetak', compact('verum_kela', 'totalPeserta', 'presensis'));
    }
}