<?php
namespace App\Http\Controllers;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TahunAkademikController extends Controller
{
    public function index() {
        $tahun_akademiks = TahunAkademik::latest()->get();
        return view('tahun-akademik.index', compact('tahun_akademiks'));
    }
    public function create() { return view('tahun-akademik.create'); }
    public function store(Request $request) {
        $request->validate([ /* ... validasi ... */ ]);
        TahunAkademik::create($request->all());
        return redirect()->route('tahun-akademik.index')->with('success', 'Tahun Akademik berhasil dibuat.');
    }
    public function edit(TahunAkademik $tahunAkademik) { return view('tahun-akademik.edit', compact('tahunAkademik')); }
    public function update(Request $request, TahunAkademik $tahunAkademik) {
        $request->validate([ /* ... validasi ... */ ]);
        $tahunAkademik->update($request->all());
        return redirect()->route('tahun-akademik.index')->with('success', 'Tahun Akademik berhasil diperbarui.');
    }
    public function destroy(TahunAkademik $tahunAkademik) {
        $tahunAkademik->delete();
        return redirect()->route('tahun-akademik.index')->with('success', 'Tahun Akademik berhasil dihapus.');
    }
    public function setActive(TahunAkademik $tahunAkademik) {
        DB::transaction(function () use ($tahunAkademik) {
            TahunAkademik::query()->update(['is_active' => false]);
            $tahunAkademik->update(['is_active' => true]);
        });
        return redirect()->route('tahun-akademik.index')->with('success', 'Tahun Akademik berhasil diaktifkan.');
    }
}