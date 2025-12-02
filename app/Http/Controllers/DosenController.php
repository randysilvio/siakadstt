<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
    /**
     * Menampilkan daftar dosen dengan Smart Filter.
     */
    public function index(Request $request): View
    {
        $query = Dosen::with('user')->latest();

        // 1. Filter Pencarian Teks (Nama, NIDN, Email)
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

        // 2. Filter Jabatan Akademik
        if ($request->filled('jabatan')) {
            $query->where('jabatan_akademik', $request->input('jabatan'));
        }

        // 3. Filter Bidang Keahlian (Pencarian Parsial)
        if ($request->filled('keahlian')) {
            $query->where('bidang_keahlian', 'like', "%{$request->input('keahlian')}%");
        }

        $dosens = $query->paginate(10)->withQueryString();

        // Ambil data unik untuk dropdown filter
        $jabatans = Dosen::select('jabatan_akademik')
                        ->whereNotNull('jabatan_akademik')
                        ->distinct()
                        ->orderBy('jabatan_akademik')
                        ->pluck('jabatan_akademik');

        return view('dosen.index', compact('dosens', 'jabatans'));
    }

    public function create(): View
    {
        return view('dosen.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nidn' => 'required|unique:dosens|max:20',
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'jabatan_akademik' => 'nullable|string|max:255',
            'bidang_keahlian' => 'nullable|string|max:255',
            'email_institusi' => 'nullable|email|max:255|unique:dosens,email_institusi',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            // Assign role dosen
            $dosenRole = \App\Models\Role::where('name', 'dosen')->first();
            if ($dosenRole) {
                $user->roles()->attach($dosenRole);
            }

            Dosen::create([
                'user_id' => $user->id,
                'nidn' => $request->nidn,
                'nama_lengkap' => $request->nama_lengkap,
                'jabatan_akademik' => $request->jabatan_akademik,
                'bidang_keahlian' => $request->bidang_keahlian,
                'email_institusi' => $request->email_institusi,
            ]);
        });

        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil ditambahkan.');
    }

    public function edit(Dosen $dosen): View
    {
        return view('dosen.edit', compact('dosen'));
    }

    public function update(Request $request, Dosen $dosen): RedirectResponse
    {
        $request->validate([
            'nidn' => 'required|max:20|unique:dosens,nidn,' . $dosen->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class . ',email,' . $dosen->user_id],
            'is_keuangan' => 'sometimes|boolean',
            'jabatan_akademik' => 'nullable|string|max:255',
            'bidang_keahlian' => 'nullable|string|max:255',
            'deskripsi_diri' => 'nullable|string',
            'email_institusi' => 'nullable|email|max:255|unique:dosens,email_institusi,' . $dosen->id,
            'link_google_scholar' => 'nullable|url',
            'link_sinta' => 'nullable|url',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($request, $dosen) {
            $dataUpdate = $request->only([
                'nidn', 'nama_lengkap', 'jabatan_akademik', 'bidang_keahlian', 
                'deskripsi_diri', 'email_institusi', 'link_google_scholar', 'link_sinta'
            ]);
            $dataUpdate['is_keuangan'] = $request->has('is_keuangan');

            if ($request->hasFile('foto_profil')) {
                if ($dosen->foto_profil) {
                    Storage::disk('public')->delete($dosen->foto_profil);
                }
                $path = $request->file('foto_profil')->store('foto-profil-dosen', 'public');
                $dataUpdate['foto_profil'] = $path;
            }

            $dosen->update($dataUpdate);

            if ($dosen->user) {
                $userData = [
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                ];
                
                // Update password jika diisi
                if ($request->filled('password')) {
                    $request->validate(['password' => ['required', 'confirmed', Rules\Password::defaults()]]);
                    $userData['password'] = Hash::make($request->password);
                }
                
                $dosen->user->update($userData);
            }
        });

        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen): RedirectResponse
    {
        DB::transaction(function () use ($dosen) {
            if ($dosen->foto_profil) {
                Storage::disk('public')->delete($dosen->foto_profil);
            }

            if ($dosen->user) {
                $dosen->user->delete();
            } else {
                $dosen->delete();
            }
        });

        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil dihapus.');
    }

    public function export() 
    {
        return Excel::download(new DosensExport, 'daftar-dosen.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        
        try {
            Excel::import(new DosensImport, $request->file('file'));
        } catch (ExcelValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            return redirect()->route('admin.dosen.index')->with('error', 'Gagal mengimpor data: ' . implode(' | ', $errorMessages));
        
        } catch (\Exception $e) {
            return redirect()->route('admin.dosen.index')->with('error', 'Terjadi kesalahan. Pastikan nama kolom di file Excel sudah benar. Pesan: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil diimpor!');
    }
    
    public function downloadTemplate()
    {
        return Excel::download(new DosenImportTemplateExport, 'template-dosen.xlsx');
    }
}