<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\TahunAkademik;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiJawaban;

class KhsController extends Controller
{
    /**
     * Menampilkan Kartu Hasil Studi (KHS) mahasiswa yang sedang login.
     * Data dikelompokkan per tahun akademik.
     */
    public function index(): View | RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pastikan pengguna memiliki data mahasiswa terkait
        if (!$user->mahasiswa) {
            abort(403, 'Hanya mahasiswa yang dapat mengakses halaman ini.');
        }
        $mahasiswa = $user->mahasiswa;

        // --- INTERCEPTOR: Cek Kewajiban Mengisi EDOM ---
        $sesiAktif = EvaluasiSesi::where('is_active', true)
                                ->where('tanggal_mulai', '<=', now())
                                ->where('tanggal_selesai', '>=', now())
                                ->first();

        if ($sesiAktif && $mahasiswa->status_krs === 'Disetujui' && $sesiAktif->tahun_akademik_id) {
            // 1. Ambil semua mata kuliah yang diambil mahasiswa pada tahun akademik sesi aktif
            $mataKuliahWajibEdom = $mahasiswa->mataKuliahs()
                ->wherePivot('tahun_akademik_id', $sesiAktif->tahun_akademik_id)
                ->pluck('mata_kuliahs.id')
                ->toArray();

            if (!empty($mataKuliahWajibEdom)) {
                // 2. Ambil daftar ID mata kuliah yang sudah diselesaikan evaluasinya di sesi ini
                $evaluasiSelesai = EvaluasiJawaban::where('mahasiswa_id', $mahasiswa->id)
                    ->where('evaluasi_sesi_id', $sesiAktif->id)
                    ->distinct()
                    ->pluck('mata_kuliah_id')
                    ->toArray();

                // 3. Cari selisih mata kuliah yang belum dievaluasi
                $belumDievaluasi = array_diff($mataKuliahWajibEdom, $evaluasiSelesai);

                // 4. Jika masih ada yang belum dievaluasi, alihkan ke halaman pengisian EDOM
                if (!empty($belumDievaluasi)) {
                    return redirect()->route('evaluasi.index')
                        ->with('error', 'PERHATIAN: Anda wajib menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM) untuk seluruh mata kuliah semester ini sebelum dapat melihat Kartu Hasil Studi (KHS).');
                }
            }
        }
        // ------------------------------------------------

        // 1. Ambil semua mata kuliah yang sudah dinilai
        // 2. Kelompokkan berdasarkan ID tahun akademik dari tabel pivot
        $krsPerTahunAkademik = $mahasiswa->mataKuliahs()
            ->withPivot('nilai', 'tahun_akademik_id')
            ->wherePivotNotNull('nilai')
            ->get()
            ->groupBy('pivot.tahun_akademik_id');

        // 3. Ambil data model TahunAkademik berdasarkan ID yang ada di KHS
        //    Ini digunakan untuk menampilkan nama tahun dan semester di view
        $tahunAkademiks = TahunAkademik::find($krsPerTahunAkademik->keys());

        // 4. Kirim semua data yang dibutuhkan ke view
        return view('khs.index', compact(
            'mahasiswa',
            'krsPerTahunAkademik',
            'tahunAkademiks'
        ));
    }
}