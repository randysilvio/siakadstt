<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\Dosen;
use Illuminate\Http\Request;

class ProgramStudiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load relasi kaprodi untuk menampilkan nama di halaman daftar
        $program_studis = ProgramStudi::with('kaprodi')->get();
        return view('program-studi.index', compact('program_studis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('program-studi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_prodi' => 'required|unique:program_studis|max:255',
        ]);
        ProgramStudi::create($request->all());
        return redirect()->route('program-studi.index')->with('success', 'Program Studi berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProgramStudi $programStudi)
    {
        // Tidak digunakan
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProgramStudi $programStudi)
    {
        $dosens = Dosen::all(); // Ambil semua data dosen
        return view('program-studi.edit', compact('programStudi', 'dosens')); // Kirim ke view
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProgramStudi $programStudi)
    {
        $request->validate([
            'nama_prodi' => 'required|max:255|unique:program_studis,nama_prodi,' . $programStudi->id,
            'kaprodi_dosen_id' => 'nullable|exists:dosens,id'
        ]);
        $programStudi->update($request->all());
        return redirect()->route('program-studi.index')->with('success', 'Program Studi berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgramStudi $programStudi)
    {
        // Cek jika masih ada mahasiswa di prodi ini
        if ($programStudi->mahasiswas()->count() > 0) {
            return redirect()->route('program-studi.index')->with('error', 'Program Studi tidak bisa dihapus karena masih memiliki mahasiswa terdaftar.');
        }
        $programStudi->delete();
        return redirect()->route('program-studi.index')->with('success', 'Program Studi berhasil dihapus!');
    }
}