<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\User;
use App\Models\Role;
use App\Models\ProgramStudi; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // [TAMBAHAN] Untuk mencatat log error
use Illuminate\Validation\Rules;
use App\Exports\DosensExport;
use App\Imports\DosensImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use App\Exports\DosenImportTemplateExport;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DosenController extends Controller
{
    public function index(Request $request): View
    {
        $query = Dosen::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nidn', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('jabatan')) {
            $query->where('jabatan_akademik', $request->input('jabatan'));
        }

        $dosens = $query->paginate(10)->withQueryString();

        $jabatans = Dosen::select('jabatan_akademik')
                        ->whereNotNull('jabatan_akademik')
                        ->distinct()
                        ->orderBy('jabatan_akademik')
                        ->pluck('jabatan_akademik');

        return view('dosen.index', compact('dosens', 'jabatans'));
    }

    public function create(): View
    {
        $programStudis = ProgramStudi::all(); 
        return view('dosen.create', compact('programStudis'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            
            'nidn' => 'nullable|unique:dosens|max:20',
            'jenis_pengajar' => 'required|string|max:100',
            'nik' => 'required|digits_between:15,16|unique:dosens,nik',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'nuptk' => 'nullable|string',
            'npwp' => 'nullable|string',
            
            'status_kepegawaian' => 'required|string',
            'program_studi_id' => 'nullable|exists:program_studis,id', 
            'jabatan_akademik' => 'nullable|string|max:255',
            'bidang_keahlian' => 'nullable|string|max:255',
            'email_institusi' => 'nullable|email|max:255|unique:dosens,email_institusi',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
                
                $dosenRole = Role::where('name', 'dosen')->first();
                if ($dosenRole) {
                    $user->roles()->attach($dosenRole);
                }

                $dosenData = $request->except(['email', 'password', 'password_confirmation', '_token', 'foto_profil']);
                $dosenData['user_id'] = $user->id;
                
                $dosenData['is_keuangan'] = $request->has('is_keuangan') ? 1 : 0;

                if ($request->hasFile('foto_profil')) {
                    $dosenData['foto_profil'] = $request->file('foto_profil')->store('foto-profil-dosen', 'public');
                }
                
                Dosen::create($dosenData);
            });

            return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil ditambahkan.');

        } catch (\Exception $e) {
            // [PERBAIKAN] Menangani error database dan mengembalikan notifikasi yang rapi
            Log::error('Gagal menambah Dosen: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan data dosen. Silakan coba lagi.');
        }
    }

    public function edit(Dosen $dosen): View
    {
        $programStudis = ProgramStudi::all(); 
        return view('dosen.edit', compact('dosen', 'programStudis'));
    }

    public function update(Request $request, Dosen $dosen): RedirectResponse
    {
        $request->validate([
            'nidn' => 'nullable|max:20|unique:dosens,nidn,' . $dosen->id,
            'jenis_pengajar' => 'required|string|max:100',
            'nik' => 'required|digits_between:15,16|unique:dosens,nik,' . $dosen->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $dosen->user_id],
            
            'jenis_kelamin' => 'required|in:L,P',
            'status_kepegawaian' => 'required|string',
            'program_studi_id' => 'nullable|exists:program_studis,id', 
            
            'email_institusi' => 'nullable|email|max:255|unique:dosens,email_institusi,' . $dosen->id,
            'link_google_scholar' => 'nullable|url',
            'link_sinta' => 'nullable|url',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $dosen) {
                $dataUpdate = $request->except(['email', 'password', 'password_confirmation', '_token', '_method', 'foto_profil']);
                
                $dataUpdate['is_keuangan'] = $request->has('is_keuangan') ? 1 : 0;

                if ($request->hasFile('foto_profil')) {
                    if ($dosen->foto_profil && Storage::disk('public')->exists($dosen->foto_profil)) {
                        Storage::disk('public')->delete($dosen->foto_profil);
                    }
                    $dataUpdate['foto_profil'] = $request->file('foto_profil')->store('foto-profil-dosen', 'public');
                }

                $dosen->update($dataUpdate);

                if ($dosen->user) {
                    $userData = ['name' => $request->nama_lengkap, 'email' => $request->email];
                    
                    if ($request->filled('password')) {
                        $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
                        $userData['password'] = Hash::make($request->password);
                    }
                    
                    $dosen->user->update($userData);
                }
            });

            return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil diperbarui.');

        } catch (\Exception $e) {
            // [PERBAIKAN] Menangani error database dan mengembalikan notifikasi yang rapi
            Log::error('Gagal memperbarui Dosen: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memperbarui data dosen. Silakan coba lagi.');
        }
    }

    public function destroy(Dosen $dosen): RedirectResponse
    {
        try {
            DB::transaction(function () use ($dosen) {
                if ($dosen->foto_profil && Storage::disk('public')->exists($dosen->foto_profil)) {
                    Storage::disk('public')->delete($dosen->foto_profil);
                }

                if ($dosen->user) {
                    $dosen->user->delete(); 
                } else {
                    $dosen->delete();
                }
            });

            return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil dihapus.');

        } catch (\Exception $e) {
            // [PERBAIKAN] Menangani kegagalan penghapusan (misal karena Foreign Key Constraint)
            Log::error('Gagal menghapus Dosen: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data. Pastikan dosen tidak sedang terikat pada jadwal mata kuliah, nilai, atau perwalian.');
        }
    }

    public function export() 
    {
        return Excel::download(new DosensExport, 'daftar-dosen.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv,txt']);
        
        try {
            Excel::import(new DosensImport, $request->file('file'));
        } catch (ExcelValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . " (" . $failure->attribute() . "): " . implode(', ', $failure->errors());
            }
            return redirect()->route('admin.dosen.index')->with('error', 'Gagal mengimpor data: ' . implode(' | ', $errorMessages));
        
        } catch (\Exception $e) {
            return redirect()->route('admin.dosen.index')->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil diimpor!');
    }
    
    public function downloadTemplate()
    {
        return Excel::download(new DosenImportTemplateExport, 'template-dosen.xlsx');
    }
}