<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\Auth;

class ValidasiKrsController extends Controller
{
    // Menampilkan detail KRS seorang mahasiswa untuk divalidasi
    public function show(Mahasiswa $mahasiswa)
    {
        $programStudi = ProgramStudi::where('kaprodi_dosen_id', Auth::user()->dosen->id)->firstOrFail();

        // Keamanan: pastikan kaprodi hanya bisa validasi mahasiswa di prodinya
        if ($mahasiswa->program_studi_id != $programStudi->id) {
            abort(403);
        }

        $mahasiswa->load('mataKuliahs.jadwals');
        return view('kaprodi.validasi_krs', compact('mahasiswa'));
    }

    // Memproses aksi validasi (setujui/tolak)
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $request->validate([
            'status_krs' => 'required|in:Disetujui,Ditolak',
        ]);

        $mahasiswa->status_krs = $request->status_krs;
        $mahasiswa->save();

        return redirect()->route('kaprodi.dashboard')->with('success', 'Status KRS mahasiswa berhasil diperbarui.');
    }
}