<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\MataKuliahsExport;
use App\Imports\MataKuliahsImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException; // DIUBAH

class MataKuliahController extends Controller
{
    public function index()
    {
        $mata_kuliahs = MataKuliah::with('dosen', 'prasyarats')->latest()->paginate(10);
        return view('mata-kuliah.index', compact('mata_kuliahs'));
    }

    public function create()
    {
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        $mata_kuliahs = MataKuliah::orderBy('nama_mk')->get();
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

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata Kuliah berhasil ditambahkan!');
    }
    
    public function show(MataKuliah $mataKuliah)
    {
        // Method ini biasanya tidak digunakan dalam CRUD resource standar
    }

    public function edit(MataKuliah $mataKuliah)
    {
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        $mata_kuliahs = MataKuliah::where('id', '!=', $mataKuliah->id)->orderBy('nama_mk')->get();
        $mataKuliah->load('jadwals');
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
            
            $mataKuliah->jadwals()->delete();
            foreach ($request->jadwals as $jadwal) {
                $mataKuliah->jadwals()->create($jadwal);
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
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        
        try {
            Excel::import(new MataKuliahsImport, $request->file('file'));
        } catch (ValidationException $e) { // DIUBAH
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

            public function __construct(array $data, array $headings)
            {
                $this->data = $data;
                $this->headings = $headings;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->headings;
            }
        };

        return Excel::download($export, 'template-mata-kuliah.xlsx');
    }
}