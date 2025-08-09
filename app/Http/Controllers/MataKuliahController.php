<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MataKuliahController extends Controller
{
    public function index()
    {
        $mata_kuliahs = MataKuliah::with('dosen', 'prasyarats')->get();
        return view('mata-kuliah.index', ['mata_kuliahs' => $mata_kuliahs]);
    }

    public function create()
    {
        $dosens = Dosen::all();
        $mata_kuliahs = MataKuliah::all();
        return view('mata-kuliah.create', compact('dosens', 'mata_kuliahs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_mk' => 'required|unique:mata_kuliahs|max:10',
            'nama_mk' => 'required|max:255',
            'sks' => 'required|integer|min:1',
            'semester' => 'required|integer|min:1|max:8',
            'dosen_id' => 'required|exists:dosens,id',
            'prasyarat_id' => 'nullable|array',
            'prasyarat_id.*' => 'exists:mata_kuliahs,id',
            'jadwals' => 'required|array|min:1',
            'jadwals.*.hari' => 'required|string',
            'jadwals.*.jam_mulai' => 'required',
            'jadwals.*.jam_selesai' => 'required',
        ]);

        DB::transaction(function () use ($request) {
            $mataKuliah = MataKuliah::create($request->except('prasyarat_id', 'jadwals'));
            $mataKuliah->prasyarats()->sync($request->prasyarat_id);

            foreach ($request->jadwals as $jadwal) {
                $mataKuliah->jadwals()->create($jadwal);
            }
        });

        return redirect('/mata-kuliah')->with('success', 'Mata Kuliah berhasil ditambahkan!');
    }
    
    public function show(MataKuliah $mataKuliah){}

    public function edit(MataKuliah $mataKuliah)
    {
        $dosens = Dosen::all();
        $mata_kuliahs = MataKuliah::where('id', '!=', $mataKuliah->id)->get();
        $mataKuliah->load('jadwals'); // Eager load jadwal
        return view('mata-kuliah.edit', compact('mataKuliah', 'dosens', 'mata_kuliahs'));
    }

    public function update(Request $request, MataKuliah $mataKuliah)
    {
        $request->validate([
            'kode_mk' => 'required|max:10|unique:mata_kuliahs,kode_mk,' . $mataKuliah->id,
            'nama_mk' => 'required|max:255',
            'sks' => 'required|integer|min:1',
            'semester' => 'required|integer|min:1|max:8',
            'dosen_id' => 'required|exists:dosens,id',
            'prasyarat_id' => 'nullable|array',
            'prasyarat_id.*' => 'exists:mata_kuliahs,id',
            'jadwals' => 'required|array|min:1',
            'jadwals.*.hari' => 'required|string',
            'jadwals.*.jam_mulai' => 'required',
            'jadwals.*.jam_selesai' => 'required',
        ]);

        DB::transaction(function () use ($request, $mataKuliah) {
            $mataKuliah->update($request->except('prasyarat_id', 'jadwals'));
            $mataKuliah->prasyarats()->sync($request->prasyarat_id);
            
            // Hapus jadwal lama dan buat yang baru
            $mataKuliah->jadwals()->delete();
            foreach ($request->jadwals as $jadwal) {
                $mataKuliah->jadwals()->create($jadwal);
            }
        });

        return redirect('/mata-kuliah')->with('success', 'Mata Kuliah berhasil diperbarui!');
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $mataKuliah->delete();
        return redirect('/mata-kuliah')->with('success', 'Mata Kuliah berhasil dihapus!');
    }
}