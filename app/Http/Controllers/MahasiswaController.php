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
                  ->orWhere('nik', 'like', "%{$search}%") // Tambahan pencarian NIK
                  ->orWhereHas('user', fn($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('program_studi_id')) {
            $query->where('program_studi_id', $request->input('program_studi_id'));
        }

        if ($request->filled('angkatan')) {
            $query->where('tahun_masuk', $request->input('angkatan'));
        }

        if ($request->filled('status')) {
            $query->where('status_mahasiswa', $request->input('status'));
        }

        $mahasiswas = $query->paginate(10)->withQueryString();
        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();
        $angkatans = Mahasiswa::select('tahun_masuk')->distinct()->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk');

        return view('mahasiswa.index', compact('mahasiswas', 'program_studis', 'angkatans'));
    }

    public function create(): View
    {
        $program_studis = ProgramStudi::all();
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        return view('mahasiswa.create', compact('program_studis', 'dosens'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Validasi Standar PDDikti Feeder
        $request->validate([
            // Akun
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            
            // Akademik
            'nim' => 'required|unique:mahasiswas|max:20',
            'program_studi_id' => 'required|exists:program_studis,id',
            'dosen_wali_id' => 'nullable|exists:dosens,id',
            'tahun_masuk' => 'required|digits:4|integer|min:1990',
            'jalur_pendaftaran' => 'nullable|string',

            // Data Pribadi (NIK Wajib)
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|digits:16|unique:mahasiswas,nik',
            'nisn' => 'nullable|digits_between:10,12',
            'kewarganegaraan' => 'required|string',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:15',

            // Alamat Detail
            'alamat' => 'nullable|string',
            'dusun' => 'nullable|string',
            'rt' => 'nullable|numeric',
            'rw' => 'nullable|numeric',
            'kelurahan' => 'nullable|string',
            'kecamatan' => 'nullable|string',
            'kode_pos' => 'nullable|numeric',
            'jenis_tinggal' => 'nullable|string',
            'alat_transportasi' => 'nullable|string',

            // Data Orang Tua
            'nama_ibu_kandung' => 'required|string|max:255', // Wajib Feeder
            'nik_ibu' => 'nullable|digits:16',
            'nik_ayah' => 'nullable|digits:16',
            'nama_ayah' => 'nullable|string',
            'penghasilan_ayah' => 'nullable|string',
            'penghasilan_ibu' => 'nullable|string',
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

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa lengkap berhasil disimpan!');
    }

    public function show(Mahasiswa $mahasiswa): RedirectResponse
    {
        return redirect()->route('admin.mahasiswa.edit', $mahasiswa);
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        $program_studis = ProgramStudi::all();
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        return view('mahasiswa.edit', compact('mahasiswa', 'program_studis', 'dosens'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            // Validasi Update (Ignore ID sendiri)
            'nim' => 'required|max:20|unique:mahasiswas,nim,' . $mahasiswa->id,
            'nik' => 'required|digits:16|unique:mahasiswas,nik,' . $mahasiswa->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $mahasiswa->user_id],
            
            'program_studi_id' => 'required|exists:program_studis,id',
            'tahun_masuk' => 'required|digits:4',
            'nama_ibu_kandung' => 'required|string', // Tetap wajib saat update
            'status_mahasiswa' => 'required|string',
            
            // Validasi field opsional lainnya bisa dilonggarkan atau disamakan dengan store
            'nik_ayah' => 'nullable|digits:16',
            'nik_ibu' => 'nullable|digits:16',
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
            return redirect()->route('admin.mahasiswa.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil diimpor!');
    }

    public function downloadImportTemplate()
    {
        return Excel::download(new MahasiswaImportTemplateExport(), 'template-impor-mahasiswa.xlsx');
    }
}