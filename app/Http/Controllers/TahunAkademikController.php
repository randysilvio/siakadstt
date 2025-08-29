<?php

namespace App\Http\Controllers;

use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rules\UniqueTahunSemesterRule;
use App\Events\TahunAkademikDiaktifkan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TahunAkademikController extends Controller
{
    public function index(): View
    {
        $tahun_akademiks = TahunAkademik::orderBy('tahun', 'desc')->orderBy('semester', 'desc')->get();
        return view('tahun-akademik.index', compact('tahun_akademiks'));
    }

    public function create(): View
    {
        $lastTahunAkademik = TahunAkademik::orderBy('tahun', 'desc')->orderBy('semester', 'desc')->first();
        $nextTahun = '';
        $nextSemester = 'Ganjil';

        if ($lastTahunAkademik) {
            if ($lastTahunAkademik->semester == 'Ganjil') {
                $nextTahun = $lastTahunAkademik->tahun;
                $nextSemester = 'Genap';
            } else {
                $years = explode('/', (string) $lastTahunAkademik->tahun);
                $nextStartYear = (int)($years[0] ?? date('Y')) + 1;
                $nextEndYear = (int)($years[1] ?? date('Y') + 1) + 1;
                $nextTahun = $nextStartYear . '/' . $nextEndYear;
                $nextSemester = 'Ganjil';
            }
        } else {
            $currentYear = date('Y');
            $nextTahun = $currentYear . '/' . ($currentYear + 1);
        }

        return view('tahun-akademik.create', compact('nextTahun', 'nextSemester'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tahun' => ['required', 'string', 'max:9', new UniqueTahunSemesterRule()],
            'semester' => ['required', 'in:Ganjil,Genap', new UniqueTahunSemesterRule()],
            'tanggal_mulai_krs' => 'required|date',
            'tanggal_selesai_krs' => 'required|date|after_or_equal:tanggal_mulai_krs',
        ]);

        TahunAkademik::create($request->all());
        return redirect()->route('admin.tahun-akademik.index')->with('success', 'Tahun Akademik berhasil dibuat.');
    }

    public function edit(TahunAkademik $tahunAkademik): View
    {
        return view('tahun-akademik.edit', compact('tahunAkademik'));
    }

    public function update(Request $request, TahunAkademik $tahunAkademik): RedirectResponse
    {
        $request->validate([
            'tahun' => ['required', 'string', 'max:9', new UniqueTahunSemesterRule($tahunAkademik->id)],
            'semester' => ['required', 'in:Ganjil,Genap', new UniqueTahunSemesterRule($tahunAkademik->id)],
            'tanggal_mulai_krs' => 'required|date',
            'tanggal_selesai_krs' => 'required|date|after_or_equal:tanggal_mulai_krs',
        ]);

        $tahunAkademik->update($request->all());
        return redirect()->route('admin.tahun-akademik.index')->with('success', 'Tahun Akademik berhasil diperbarui.');
    }

    public function destroy(TahunAkademik $tahunAkademik): RedirectResponse
    {
        if ($tahunAkademik->is_active) {
            return back()->with('error', 'Tidak dapat menghapus tahun akademik yang sedang aktif.');
        }

        $tahunAkademik->delete();
        return redirect()->route('admin.tahun-akademik.index')->with('success', 'Tahun Akademik berhasil dihapus.');
    }

    public function setActive(TahunAkademik $tahunAkademik): RedirectResponse
    {
        DB::transaction(function () use ($tahunAkademik) {
            TahunAkademik::query()->update(['is_active' => false]);
            $tahunAkademik->update(['is_active' => true]);
        });
        
        event(new TahunAkademikDiaktifkan($tahunAkademik));

        return redirect()->route('admin.tahun-akademik.index')->with('success', "Tahun Akademik {$tahunAkademik->tahun} Semester {$tahunAkademik->semester} berhasil diaktifkan.");
    }
}
