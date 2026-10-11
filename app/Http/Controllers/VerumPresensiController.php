<?php

namespace App\Http\Controllers;

use App\Models\VerumKelas;
use App\Models\VerumPresensi;
use App\Models\VerumKehadiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View; // Pastikan menggunakan library View

class VerumPresensiController extends Controller
{
    /**
     * Dosen membuka sesi presensi baru.
     */
    public function store(Request $request, VerumKelas $verum_kela): RedirectResponse
    {
        if (!Auth::user()->dosen || Auth::user()->dosen->id !== $verum_kela->dosen_id) {
            abort(403, 'Hanya dosen pengampu yang bisa membuka presensi.');
        }

        $request->validate([
            'judul_pertemuan' => 'required|string|max:255',
            'pertemuan_ke' => 'required|integer|min:1',
            'durasi' => 'required|integer|min:1',
        ]);

        $verum_kela->presensi()->create([
            'judul_pertemuan' => $request->judul_pertemuan,
            'pertemuan_ke' => $request->pertemuan_ke,
            'waktu_buka' => now(),
            'waktu_tutup' => now()->addMinutes((int)$request->durasi),
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

        if (!$mahasiswa) {
            abort(403, 'Aksi ini hanya untuk mahasiswa.');
        }

        // Validasi Keamanan: Pastikan mahasiswa terdaftar di kelas ini
        $isEnrolled = $mahasiswa->mataKuliahs->contains($verum_presensi->kelas->mata_kuliah_id);
        if (!$isEnrolled) {
            return back()->with('error', 'Anda tidak terdaftar di kelas ini.');
        }

        // Cek Batas Waktu
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
     * Menampilkan dan menyiapkan data halaman Cetak Rekap Absensi untuk Dosen
     */
    public function cetakRekap(VerumKelas $verum_kela): View
    {
        if (!Auth::user()->dosen || Auth::user()->dosen->id !== $verum_kela->dosen_id) {
            abort(403, 'Hanya dosen pengampu yang bisa mencetak absensi.');
        }

        // Mengambil data seluruh mahasiswa yang KRS-nya aktif pada mata kuliah ini
        $mahasiswas = $verum_kela->mataKuliah->mahasiswas()->orderBy('nama_lengkap')->get();
        
        // Mengambil seluruh data pertemuan sekaligus *eager load* tabel kehadirannya
        $presensis = $verum_kela->presensi()->with('kehadiran')->orderBy('pertemuan_ke')->get();

        return view('verum.presensi_cetak', compact('verum_kela', 'mahasiswas', 'presensis'));
    }
}