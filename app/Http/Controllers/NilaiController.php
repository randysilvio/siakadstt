<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\TahunAkademik; // <-- Tambahkan ini
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class NilaiController extends Controller
{
    /**
     * Menampilkan daftar mata kuliah untuk dipilih (HANYA UNTUK ADMIN).
     */
    public function index()
    {
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'AKSI TIDAK DIIZINKAN.');
        }
        $mata_kuliahs = MataKuliah::with('dosen')->get();
        return view('nilai.index', compact('mata_kuliahs'));
    }

    /**
     * Menampilkan form untuk input nilai (ADMIN, DOSEN PENGAMPU & KAPRODI).
     */
    public function show(MataKuliah $mataKuliah)
    {
        // Otorisasi menggunakan Gate yang sudah kita definisikan
        $this->authorize('inputNilai', $mataKuliah);

        // =================================================================
        // ===== PERBAIKAN: Filter Mahasiswa Berdasarkan Semester Aktif =====
        // =================================================================
        // 1. Ambil tahun akademik yang sedang aktif.
        $tahunAkademikAktif = TahunAkademik::where('is_active', 1)->first();

        // Jika tidak ada semester aktif, jangan tampilkan mahasiswa.
        if (!$tahunAkademikAktif) {
            // Mengatur relasi mahasiswas menjadi koleksi kosong
            $mataKuliah->setRelation('mahasiswas', collect());
            // Menambahkan pesan error untuk ditampilkan di view
            session()->flash('error', 'Tidak ada Tahun Akademik yang aktif. Silakan hubungi Administrator.');
        } else {
            // 2. Muat relasi 'mahasiswas' HANYA untuk mahasiswa yang mengambil
            //    mata kuliah ini di semester yang sedang aktif.
            $mataKuliah->load(['mahasiswas' => function ($query) use ($tahunAkademikAktif) {
                $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademikAktif->id);
            }]);
        }
        
        return view('nilai.show', compact('mataKuliah'));
    }

    /**
     * Menyimpan nilai yang diinput (ADMIN, DOSEN PENGAMPU & KAPRODI).
     */
    public function store(Request $request)
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'nilai.*' => 'nullable|string|max:2|in:A,B,C,D,E', // Validasi nilai yang diizinkan
        ]);
    
        $mataKuliah = MataKuliah::find($request->mata_kuliah_id);

        // Otorisasi menggunakan Gate sebelum menyimpan
        $this->authorize('inputNilai', $mataKuliah);

        // =================================================================
        // ===== PERBAIKAN: Simpan Nilai Berdasarkan Semester Aktif =====
        // =================================================================
        // 1. Ambil tahun akademik yang sedang aktif.
        $tahunAkademikAktif = TahunAkademik::where('is_active', 1)->firstOrFail();
        
        foreach ($request->nilai as $mahasiswa_id => $nilai) {
            // 2. Gunakan wherePivot untuk memastikan kita HANYA mengupdate
            //    record nilai untuk semester yang aktif.
            $mataKuliah->mahasiswas()
                ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
                ->updateExistingPivot($mahasiswa_id, ['nilai' => strtoupper($nilai)]);
        }

        // Mengarahkan kembali ke halaman yang sama dengan pesan sukses.
        return redirect()->route('nilai.show', $mataKuliah->id)->with('success', 'Nilai berhasil disimpan!');
    }
}
