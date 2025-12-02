<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Exports\MahasiswasExport;
use App\Imports\MahasiswasImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use App\Exports\MahasiswaImportTemplateExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    /**
     * Menampilkan daftar mahasiswa dengan Smart Filter.
     */
    public function index(Request $request): View
    {
        $query = Mahasiswa::with(['programStudi', 'user.roles'])->latest();

        // 1. Filter Pencarian Teks (Nama, NIM, Email)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
            });
        }

        // 2. Filter Program Studi
        if ($request->filled('program_studi_id')) {
            $query->where('program_studi_id', $request->input('program_studi_id'));
        }

        // 3. [BARU] Filter Angkatan (Tahun Masuk)
        if ($request->filled('angkatan')) {
            $query->where('tahun_masuk', $request->input('angkatan'));
        }

        // 4. [BARU] Filter Status Mahasiswa
        if ($request->filled('status')) {
            $query->where('status_mahasiswa', $request->input('status'));
        }

        $mahasiswas = $query->paginate(10)->withQueryString();
        
        // Data untuk Dropdown Filter
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        
        // [BARU] Ambil daftar tahun masuk unik untuk filter angkatan
        $angkatans = Mahasiswa::select('tahun_masuk')
                        ->distinct()
                        ->orderBy('tahun_masuk', 'desc')
                        ->pluck('tahun_masuk');

        return view('mahasiswa.index', compact('mahasiswas', 'program_studis', 'angkatans'));
    }

    public function create(): View
    {
        $program_studis = ProgramStudi::all();
        $dosens = Dosen::all();
        return view('mahasiswa.create', compact('program_studis', 'dosens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nim' => 'required|unique:mahasiswas|max:10',
            'nama_lengkap' => 'required|string|max:255',
            'program_studi_id' => 'required|exists:program_studis,id',
            'dosen_wali_id' => 'nullable|exists:dosens,id',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:15',
            'tahun_masuk' => 'required|digits:4|integer|min:1990',
            'nama_ibu_kandung' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            $user->roles()->attach(\App\Models\Role::where('name', 'mahasiswa')->first());

            $mahasiswaData = $request->except(['email', 'password', 'password_confirmation', '_token']);
            $mahasiswaData['user_id'] = $user->id;
            $mahasiswaData['status_mahasiswa'] = 'Aktif';

            Mahasiswa::create($mahasiswaData);
        });

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa dan akun login berhasil dibuat!');
    }

    public function show(Mahasiswa $mahasiswa): RedirectResponse
    {
        return redirect()->route('admin.mahasiswa.edit', $mahasiswa);
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        $program_studis = ProgramStudi::all();
        $dosens = Dosen::all();
        return view('mahasiswa.edit', compact('mahasiswa', 'program_studis', 'dosens'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'nim' => 'required|max:10|unique:mahasiswas,nim,' . $mahasiswa->id,
            'nama_lengkap' => 'required|string|max:255',
            'program_studi_id' => 'required|exists:program_studis,id',
            'dosen_wali_id' => 'nullable|exists:dosens,id',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class . ',email,' . $mahasiswa->user_id],
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:15',
            'tahun_masuk' => 'required|digits:4|integer|min:1990',
            'nama_ibu_kandung' => 'nullable|string|max:255',
            'status_mahasiswa' => 'required|string',
        ]);
    
        DB::transaction(function () use ($request, $mahasiswa) {
            $mahasiswa->update($request->except(['email', 'password', 'password_confirmation', '_token', '_method']));
    
            if ($mahasiswa->user) {
                $userData = [
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                ];
                if ($request->filled('password')) {
                    $request->validate(['password' => ['required', 'confirmed', Rules\Password::defaults()]]);
                    $userData['password'] = Hash::make($request->password);
                }
                $mahasiswa->user->update($userData);
            }
        });
    
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui!');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        DB::transaction(function () use ($mahasiswa) {
            if ($mahasiswa->user) {
                $mahasiswa->user->delete();
            }
            $mahasiswa->delete();
        });

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $program_studi_id = $request->input('program_studi_id');
        // Catatan: Jika ingin filter angkatan/status ikut terekspor, update juga constructor MahasiswasExport
        return Excel::download(new MahasiswasExport($search, $program_studi_id), 'mahasiswa.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        try {
            Excel::import(new MahasiswasImport, $request->file('file'));
        } catch (ExcelValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            return redirect()->route('admin.mahasiswa.index')->with('error', 'Gagal mengimpor data: ' . implode(' | ', $errorMessages));
        } catch (\Exception $e) {
            return redirect()->route('admin.mahasiswa.index')->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil diimpor!');
    }

    public function downloadImportTemplate()
    {
        return Excel::download(new MahasiswaImportTemplateExport(), 'template-impor-mahasiswa.xlsx');
    }
}