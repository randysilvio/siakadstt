<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Kurikulum;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreMataKuliahRequest;
use App\Http\Requests\UpdateMataKuliahRequest;
use App\Exports\MataKuliahsExport;
use App\Imports\MataKuliahsImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Http\Request; // <-- Tambahkan ini

class MataKuliahController extends Controller
{
    public function index(Request $request) // <-- Tambahkan Request
    {
        // =================================================================
        // ===== PERBAIKAN: Menambahkan Logika Pencarian & Filter =====
        // =================================================================
        $query = MataKuliah::with('dosen', 'kurikulum', 'prasyarats')->latest();

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_mk', 'like', "%{$search}%")
                  ->orWhere('kode_mk', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->input('semester'));
        }

        $mata_kuliahs = $query->paginate(10)->withQueryString();
        // =================================================================

        return view('mata-kuliah.index', compact('mata_kuliahs'));
    }

    public function create()
    {
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        $kurikulums = Kurikulum::orderBy('tahun', 'desc')->get();
        $mata_kuliahs = MataKuliah::select('id', 'nama_mk', 'semester')->orderBy('semester')->orderBy('nama_mk')->get();
        return view('mata-kuliah.create', compact('dosens', 'kurikulums', 'mata_kuliahs'));
    }

    public function store(StoreMataKuliahRequest $request)
    {
        DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $mataKuliah = MataKuliah::create($validated);
            
            if ($request->has('prasyarat_id')) {
                $mataKuliah->prasyarats()->sync($request->prasyarat_id);
            }

            if ($request->has('jadwals')) {
                foreach ($request->jadwals as $jadwal) {
                    $mataKuliah->jadwals()->create($jadwal);
                }
            }
        });

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata Kuliah berhasil ditambahkan!');
    }

    public function edit(MataKuliah $mataKuliah)
    {
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        $kurikulums = Kurikulum::orderBy('tahun', 'desc')->get();
        $mata_kuliahs_options = MataKuliah::select('id', 'nama_mk', 'semester')
                                  ->where('id', '!=', $mataKuliah->id)
                                  ->orderBy('semester')
                                  ->orderBy('nama_mk')
                                  ->get();
        $mataKuliah->load('jadwals', 'prasyarats');
        return view('mata-kuliah.edit', compact('mataKuliah', 'dosens', 'kurikulums', 'mata_kuliahs_options'));
    }

    public function update(UpdateMataKuliahRequest $request, MataKuliah $mataKuliah)
    {
        DB::transaction(function () use ($request, $mataKuliah) {
            $validated = $request->validated();
            $mataKuliah->update($validated);
            $mataKuliah->prasyarats()->sync($request->prasyarat_id ?? []);
            
            $mataKuliah->jadwals()->delete();
            if ($request->has('jadwals')) {
                foreach ($request->jadwals as $jadwal) {
                    $mataKuliah->jadwals()->create($jadwal);
                }
            }
        });

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata Kuliah berhasil diperbarui!');
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $mataKuliah->delete();
        return redirect()->route('mata-kuliah.index')->with('success', 'Mata Kuliah berhasil dihapus!');
    }

    public function export() 
    {
        return Excel::download(new MataKuliahsExport, 'daftar-mata-kuliah.xlsx');
    }

    public function import(Request $request) 
    {
        $request->validate([ 'file' => 'required|mimes:xlsx,xls' ]);
        try {
            Excel::import(new MataKuliahsImport, $request->file('file'));
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            return redirect()->route('mata-kuliah.index')->with('error', 'Gagal mengimpor data: ' . implode(' | ', $errorMessages));
        } catch (\Exception $e) {
            return redirect()->route('mata-kuliah.index')->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
        return redirect()->route('mata-kuliah.index')->with('success', 'Data mata kuliah berhasil diimpor!');
    }

    public function downloadTemplate()
    {
        $headings = ['kode_mk', 'nama_mk', 'sks', 'semester', 'nidn_dosen'];
        $data = [['MK001', 'Teologi Sistematika 1', '3', '1', '1234567890']];
        $export = new class($data, $headings) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $data;
            protected $headings;
            public function __construct(array $data, array $headings) { $this->data = $data; $this->headings = $headings; }
            public function array(): array { return $this->data; }
            public function headings(): array { return $this->headings; }
        };
        return Excel::download($export, 'template-mata-kuliah.xlsx');
    }
}
