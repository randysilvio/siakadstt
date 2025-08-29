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
    public function index(Request $request): View
    {
        $query = Mahasiswa::with(['programStudi', 'user.roles'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('program_studi_id')) {
            $query->where('program_studi_id', $request->input('program_studi_id'));
        }

        $mahasiswas = $query->paginate(10)->withQueryString();
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();

        return view('mahasiswa.index', compact('mahasiswas', 'program_studis'));
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
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            // PERBAIKAN: Menggunakan relasi untuk menetapkan role
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
        ]);

        DB::transaction(function () use ($request, $mahasiswa) {
            $mahasiswa->update($request->except(['email', '_token', '_method']));

            if ($mahasiswa->user) {
                $mahasiswa->user->update([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                ]);
            }
        });

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui!');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        DB::transaction(function () use ($mahasiswa) {
            if ($mahasiswa->user) {
                $mahasiswa->user->delete();
            } else {
                $mahasiswa->delete();
            }
        });

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $program_studi_id = $request->input('program_studi_id');
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
