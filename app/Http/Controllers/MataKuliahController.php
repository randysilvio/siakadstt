<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Dosen;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreMataKuliahRequest;
use App\Http\Requests\UpdateMataKuliahRequest;
use App\Exports\MataKuliahsExport;
use App\Imports\MataKuliahsImport;
use App\Exports\MataKuliahImportTemplateExport; // Pastikan export template ini ada
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    /**
     * Menampilkan daftar mata kuliah dengan Smart Filter.
     */
    public function index(Request $request): View
    {
        $query = MataKuliah::with(['dosen.user', 'kurikulum', 'prasyarats'])->latest();

        // 1. Filter Pencarian Teks (Kode MK, Nama MK)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_mk', 'like', "%{$search}%")
                  ->orWhere('kode_mk', 'like', "%{$search}%");
            });
        }

        // 2. Filter Semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->input('semester'));
        }

        // 3. Filter Kurikulum
        if ($request->filled('kurikulum_id')) {
            $query->where('kurikulum_id', $request->input('kurikulum_id'));
        }

        $mata_kuliahs = $query->paginate(10)->withQueryString();
        
        // Data untuk Dropdown Filter Kurikulum
        $kurikulums = Kurikulum::orderBy('tahun', 'desc')->get();

        return view('mata-kuliah.index', compact('mata_kuliahs', 'kurikulums'));
    }

    public function create(): View
    {
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        // Ambil kurikulum, urutkan yang aktif atau terbaru di atas
        $kurikulums = Kurikulum::orderBy('is_active', 'desc')->orderBy('tahun', 'desc')->get();
        $mata_kuliahs = MataKuliah::select('id', 'nama_mk', 'semester')->orderBy('semester')->orderBy('nama_mk')->get();
        
        return view('mata-kuliah.create', compact('dosens', 'kurikulums', 'mata_kuliahs'));
    }

    public function store(StoreMataKuliahRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $mataKuliah = MataKuliah::create($validated);
            
            // Simpan Prasyarat (Many-to-Many)
            if ($request->has('prasyarat_id')) {
                $mataKuliah->prasyarats()->sync($request->prasyarat_id);
            }

            // Simpan Jadwal (One-to-Many)
            if ($request->has('jadwals')) {
                foreach ($request->jadwals as $jadwal) {
                    if (!empty($jadwal['hari']) && !empty($jadwal['jam_mulai']) && !empty($jadwal['jam_selesai'])) {
                        $mataKuliah->jadwals()->create($jadwal);
                    }
                }
            }
        });

        return redirect()->route('admin.mata-kuliah.index')->with('success', 'Mata Kuliah berhasil ditambahkan!');
    }

    public function edit(MataKuliah $mataKuliah): View
    {
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        $kurikulums = Kurikulum::orderBy('tahun', 'desc')->get();
        
        // Opsi mata kuliah prasyarat (kecuali dirinya sendiri)
        $mata_kuliahs_options = MataKuliah::select('id', 'nama_mk', 'semester')
                                  ->where('id', '!=', $mataKuliah->id)
                                  ->orderBy('semester')
                                  ->orderBy('nama_mk')
                                  ->get();
                                  
        $mataKuliah->load('jadwals', 'prasyarats');
        
        return view('mata-kuliah.edit', compact('mataKuliah', 'dosens', 'kurikulums', 'mata_kuliahs_options'));
    }

    public function update(UpdateMataKuliahRequest $request, MataKuliah $mataKuliah): RedirectResponse
    {
        DB::transaction(function () use ($request, $mataKuliah) {
            $validated = $request->validated();
            $mataKuliah->update($validated);
            
            // Update Prasyarat
            $mataKuliah->prasyarats()->sync($request->input('prasyarat_id', []));
            
            // Update Jadwal (Hapus lama, buat baru)
            $mataKuliah->jadwals()->delete();
            if ($request->has('jadwals')) {
                foreach ($request->jadwals as $jadwal) {
                    if (!empty($jadwal['hari']) && !empty($jadwal['jam_mulai']) && !empty($jadwal['jam_selesai'])) {
                        $mataKuliah->jadwals()->create($jadwal);
                    }
                }
            }
        });

        return redirect()->route('admin.mata-kuliah.index')->with('success', 'Mata Kuliah berhasil diperbarui!');
    }

    public function destroy(MataKuliah $mataKuliah): RedirectResponse
    {
        // Hapus prasyarat & jadwal otomatis via cascade di database atau model events, 
        // tapi kita bisa detach manual untuk keamanan level aplikasi
        $mataKuliah->prasyarats()->detach();
        $mataKuliah->jadwals()->delete();
        $mataKuliah->delete();

        return redirect()->route('admin.mata-kuliah.index')->with('success', 'Mata Kuliah berhasil dihapus!');
    }

    public function export()
    {
        return Excel::download(new MataKuliahsExport, 'daftar-mata-kuliah.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        
        try {
            Excel::import(new MataKuliahsImport, $request->file('file'));
        } catch (ExcelValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            return redirect()->route('admin.mata-kuliah.index')->with('error', 'Gagal mengimpor data: ' . implode(' | ', $errorMessages));
        } catch (\Exception $e) {
            return redirect()->route('admin.mata-kuliah.index')->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.mata-kuliah.index')->with('success', 'Data mata kuliah berhasil diimpor!');
    }

    public function downloadTemplate()
    {
        // Pastikan Anda sudah membuat class Export ini: php artisan make:export MataKuliahImportTemplateExport
        return Excel::download(new MataKuliahImportTemplateExport, 'template-mata-kuliah.xlsx');
    }
}