<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiSesi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;

class EvaluasiSesiController extends Controller
{
    public function index()
    {
        $sesi = EvaluasiSesi::with('tahunAkademik')->latest()->paginate(10);
        return view('admin.evaluasi.sesi.index', compact('sesi'));
    }

    public function create()
    {
        // PERBAIKAN: Mengubah 'nama_tahun_akademik' menjadi 'tahun' sesuai skema database
        $tahunAkademik = TahunAkademik::orderBy('tahun', 'desc')->get();
        return view('admin.evaluasi.sesi.create', compact('tahunAkademik'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sesi' => 'required|string|max:255',
            'tahun_akademik_id' => 'required|exists:tahun_akademiks,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'is_active' => 'sometimes|boolean',
        ]);

        EvaluasiSesi::create([
            'nama_sesi' => $request->nama_sesi,
            'tahun_akademik_id' => $request->tahun_akademik_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('evaluasi-sesi.index')->with('success', 'Sesi evaluasi berhasil dibuat.');
    }

    public function edit(EvaluasiSesi $evaluasi_sesi)
    {
        // PERBAIKAN: Mengubah 'nama_tahun_akademik' menjadi 'tahun' sesuai skema database
        $tahunAkademik = TahunAkademik::orderBy('tahun', 'desc')->get();
        return view('admin.evaluasi.sesi.edit', [
            'sesi' => $evaluasi_sesi,
            'tahunAkademik' => $tahunAkademik
        ]);
    }

    public function update(Request $request, EvaluasiSesi $evaluasi_sesi)
    {
        $request->validate([
            'nama_sesi' => 'required|string|max:255',
            'tahun_akademik_id' => 'required|exists:tahun_akademiks,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $evaluasi_sesi->update([
            'nama_sesi' => $request->nama_sesi,
            'tahun_akademik_id' => $request->tahun_akademik_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('evaluasi-sesi.index')->with('success', 'Sesi evaluasi berhasil diperbarui.');
    }

    public function destroy(EvaluasiSesi $evaluasi_sesi)
    {
        // Tambahkan pengecekan jika sesi memiliki jawaban, untuk mencegah penghapusan
        // if ($evaluasi_sesi->jawaban()->exists()) {
        //     return back()->with('error', 'Sesi ini tidak dapat dihapus karena sudah memiliki data jawaban.');
        // }
        
        $evaluasi_sesi->delete();

        return redirect()->route('evaluasi-sesi.index')->with('success', 'Sesi evaluasi berhasil dihapus.');
    }
}
