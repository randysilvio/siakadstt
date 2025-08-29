<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NilaiController extends Controller
{
    /**
     * Menampilkan daftar mata kuliah untuk dipilih (HANYA UNTUK ADMIN).
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Menggunakan Gate untuk otorisasi yang lebih bersih
        $this->authorize('viewAny', MataKuliah::class);

        $mata_kuliahs = MataKuliah::with('dosen.user')->get();
        return view('nilai.index', compact('mata_kuliahs'));
    }

    /**
     * Menampilkan form untuk input nilai (ADMIN, DOSEN PENGAMPU & KAPRODI).
     */
    public function show(MataKuliah $mataKuliah): View
    {
        // Otorisasi menggunakan Gate yang sudah kita definisikan
        $this->authorize('inputNilai', $mataKuliah);

        // Ambil tahun akademik yang sedang aktif.
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();

        if (!$tahunAkademikAktif) {
            // Mengatur relasi mahasiswas menjadi koleksi kosong jika tidak ada semester aktif
            $mataKuliah->setRelation('mahasiswas', collect());
            session()->flash('error', 'Tidak ada Tahun Akademik yang aktif. Silakan hubungi Administrator.');
        } else {
            // Muat relasi 'mahasiswas' HANYA untuk mahasiswa yang mengambil
            // mata kuliah ini di semester yang sedang aktif.
            $mataKuliah->load(['mahasiswas' => function ($query) use ($tahunAkademikAktif) {
                $query->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademikAktif->id);
            }]);
        }
        
        return view('nilai.show', compact('mataKuliah'));
    }

    /**
     * Menyimpan nilai yang diinput (ADMIN, DOSEN PENGAMPU & KAPRODI).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'nilai.*' => 'nullable|string|max:2|in:A,B,C,D,E',
        ]);
    
        $mataKuliah = MataKuliah::findOrFail($request->mata_kuliah_id);

        // Otorisasi menggunakan Gate sebelum menyimpan
        $this->authorize('inputNilai', $mataKuliah);

        // Ambil tahun akademik yang sedang aktif.
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->firstOrFail();
        
        if ($request->has('nilai')) {
            foreach ($request->nilai as $mahasiswa_id => $nilai) {
                // Gunakan wherePivot untuk memastikan kita HANYA mengupdate
                // record nilai untuk semester yang aktif.
                $mataKuliah->mahasiswas()
                    ->wherePivot('tahun_akademik_id', $tahunAkademikAktif->id)
                    ->updateExistingPivot($mahasiswa_id, ['nilai' => $nilai ? strtoupper($nilai) : null]);
            }
        }

        return redirect()->route('nilai.show', $mataKuliah->id)->with('success', 'Nilai berhasil disimpan!');
    }
}
