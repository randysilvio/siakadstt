<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KurikulumController extends Controller
{
    public function index()
    {
        $kurikulums = Kurikulum::orderBy('tahun', 'desc')->get();
        return view('admin.kurikulum.index', compact('kurikulums'));
    }

    public function create()
    {
        return view('admin.kurikulum.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kurikulum' => 'required|string|max:255|unique:kurikulums',
            'tahun' => 'required|digits:4|integer|min:2000',
        ]);

        Kurikulum::create($request->all());
        return redirect()->route('kurikulum.index')->with('success', 'Kurikulum berhasil dibuat.');
    }

    public function edit(Kurikulum $kurikulum)
    {
        return view('admin.kurikulum.edit', compact('kurikulum'));
    }

    public function update(Request $request, Kurikulum $kurikulum)
    {
        $request->validate([
            'nama_kurikulum' => 'required|string|max:255|unique:kurikulums,nama_kurikulum,' . $kurikulum->id,
            'tahun' => 'required|digits:4|integer|min:2000',
        ]);

        $kurikulum->update($request->all());
        return redirect()->route('kurikulum.index')->with('success', 'Kurikulum berhasil diperbarui.');
    }

    public function destroy(Kurikulum $kurikulum)
    {
        if ($kurikulum->is_active) {
            return back()->with('error', 'Tidak dapat menghapus kurikulum yang sedang aktif.');
        }
        // Tambahkan pengecekan jika kurikulum sudah digunakan oleh mata kuliah
        if ($kurikulum->mataKuliahs()->exists()) {
             return back()->with('error', 'Kurikulum ini sudah digunakan oleh beberapa mata kuliah dan tidak dapat dihapus.');
        }
        $kurikulum->delete();
        return redirect()->route('kurikulum.index')->with('success', 'Kurikulum berhasil dihapus.');
    }

    public function setActive(Kurikulum $kurikulum)
    {
        DB::transaction(function () use ($kurikulum) {
            Kurikulum::query()->update(['is_active' => false]);
            $kurikulum->update(['is_active' => true]);
        });
        return redirect()->route('kurikulum.index')->with('success', "Kurikulum {$kurikulum->nama_kurikulum} berhasil diaktifkan.");
    }
}