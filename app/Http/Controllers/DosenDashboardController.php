<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Pastikan Import Storage
use App\Models\ProgramStudi;
use App\Models\MataKuliah; // Import Model

class DosenDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:dosen');
    }
    
    public function index()
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403, 'Data dosen tidak ditemukan.');
        }
        
        $mata_kuliahs = $dosen->mataKuliahs()->withCount('mahasiswas')->get();
        $jumlahMahasiswaWali = $dosen->mahasiswaWali()->count();

        $prodiYangDikepalai = ProgramStudi::where('kaprodi_dosen_id', $dosen->id)->first();

        return view('dosen.dashboard', compact('dosen', 'mata_kuliahs', 'jumlahMahasiswaWali', 'prodiYangDikepalai'));
    }

    /**
     * --- TAMBAHAN: Proses Upload RPS ---
     */
    public function uploadRps(Request $request, MataKuliah $mataKuliah)
    {
        // Validasi Kepemilikan (Security Check)
        $dosen = Auth::user()->dosen;
        if ($mataKuliah->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mengubah RPS mata kuliah ini.');
        }

        $request->validate([
            'file_rps' => 'required|mimes:pdf|max:5120', // Maks 5MB, hanya PDF
        ]);

        if ($request->hasFile('file_rps')) {
            // Hapus file lama jika ada
            if ($mataKuliah->file_rps && Storage::disk('public')->exists($mataKuliah->file_rps)) {
                Storage::disk('public')->delete($mataKuliah->file_rps);
            }

            // Simpan file baru
            $path = $request->file('file_rps')->store('rps', 'public');
            
            $mataKuliah->update([
                'file_rps' => $path
            ]);
        }

        return redirect()->back()->with('success', 'File RPS berhasil diunggah.');
    }
}