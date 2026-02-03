<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use App\Models\ProgramStudi; // [TAMBAH] Import Prodi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KurikulumController extends Controller
{
    public function index()
    {
        // Load relasi programStudi agar bisa ditampilkan di tabel
        $kurikulums = Kurikulum::with('programStudi')->orderBy('tahun', 'desc')->get();
        return view('admin.kurikulum.index', compact('kurikulums'));
    }

    public function create()
    {
        // [TAMBAH] Kirim data prodi ke view create
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        return view('admin.kurikulum.create', compact('program_studis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kurikulum' => 'required|string|max:255', // Hapus unique global jika nama sama boleh beda prodi
            'tahun' => 'required|digits:4|integer|min:2000',
            'program_studi_id' => 'required|exists:program_studis,id', // [TAMBAH] Validasi Prodi
        ]);

        Kurikulum::create($request->all());
        return redirect()->route('admin.kurikulum.index')->with('success', 'Kurikulum berhasil dibuat.');
    }

    public function edit(Kurikulum $kurikulum)
    {
        // [TAMBAH] Kirim data prodi ke view edit
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        return view('admin.kurikulum.edit', compact('kurikulum', 'program_studis'));
    }

    public function update(Request $request, Kurikulum $kurikulum)
    {
        $request->validate([
            'nama_kurikulum' => 'required|string|max:255',
            'tahun' => 'required|digits:4|integer|min:2000',
            'program_studi_id' => 'required|exists:program_studis,id', // [TAMBAH] Validasi Prodi
        ]);

        $kurikulum->update($request->all());
        return redirect()->route('admin.kurikulum.index')->with('success', 'Kurikulum berhasil diperbarui.');
    }

    public function destroy(Kurikulum $kurikulum)
    {
        if ($kurikulum->is_active) {
            return back()->with('error', 'Tidak dapat menghapus kurikulum yang sedang aktif.');
        }
        
        if ($kurikulum->mataKuliahs()->exists()) {
             return back()->with('error', 'Kurikulum ini sudah digunakan oleh beberapa mata kuliah dan tidak dapat dihapus.');
        }

        $kurikulum->delete();
        return redirect()->route('admin.kurikulum.index')->with('success', 'Kurikulum berhasil dihapus.');
    }

    public function setActive(Kurikulum $kurikulum)
    {
        DB::transaction(function () use ($kurikulum) {
            // Nonaktifkan kurikulum lain DI PRODI YANG SAMA (agar tidak konflik antar prodi)
            if ($kurikulum->program_studi_id) {
                Kurikulum::where('program_studi_id', $kurikulum->program_studi_id)
                         ->update(['is_active' => false]);
            } else {
                // Fallback jika global
                Kurikulum::query()->update(['is_active' => false]);
            }
            
            $kurikulum->update(['is_active' => true]);
        });
        return redirect()->route('admin.kurikulum.index')->with('success', "Kurikulum {$kurikulum->nama_kurikulum} berhasil diaktifkan.");
    }
}