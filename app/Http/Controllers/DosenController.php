<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\User;
use App\Models\Role; // Pastikan Model Role diimport
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
     * Menampilkan daftar dosen dengan fitur pencarian dan filter.
     */
    public function index(Request $request): View
    {
        $query = Dosen::with('user')->latest();

        // Logika Pencarian
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

        // Logika Filter Jabatan
        if ($request->filled('jabatan')) {
            $query->where('jabatan_akademik', $request->input('jabatan'));
        }

        $dosens = $query->paginate(10)->withQueryString();

        // Ambil daftar jabatan unik untuk dropdown filter
        $jabatans = Dosen::select('jabatan_akademik')
                        ->whereNotNull('jabatan_akademik')
                        ->distinct()
                        ->orderBy('jabatan_akademik')
                        ->pluck('jabatan_akademik');

        return view('dosen.index', compact('dosens', 'jabatans'));
    }

    /**
     * Menampilkan form tambah dosen.
     */
    public function create(): View
    {
        return view('dosen.create');
    }

    /**
     * Menyimpan data dosen baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            // Validasi Akun
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            
            // Validasi Identitas Utama
            'nidn' => 'required|unique:dosens|max:20',
            'nik' => 'required|digits:16|unique:dosens,nik', // Wajib untuk Feeder
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            
            // Validasi Opsional
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'nuptk' => 'nullable|string',
            'npwp' => 'nullable|string',
            
            // Validasi Kepegawaian & Akademik
            'status_kepegawaian' => 'required|string',
            'jabatan_akademik' => 'nullable|string|max:255',
            'bidang_keahlian' => 'nullable|string|max:255',
            'email_institusi' => 'nullable|email|max:255|unique:dosens,email_institusi',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Buat User Login
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            // Assign Role Dosen
            $dosenRole = Role::where('name', 'dosen')->first();
            if ($dosenRole) {
                $user->roles()->attach($dosenRole);
            }

            // 2. Siapkan Data Dosen
            $dosenData = $request->except(['email', 'password', 'password_confirmation', '_token', 'foto_profil']);
            $dosenData['user_id'] = $user->id;
            
            // Ceklis Keuangan
            $dosenData['is_keuangan'] = $request->has('is_keuangan') ? 1 : 0;

            // Upload Foto jika ada
            if ($request->hasFile('foto_profil')) {
                $dosenData['foto_profil'] = $request->file('foto_profil')->store('foto-profil-dosen', 'public');
            }
            
            // Simpan Data Dosen
            Dosen::create($dosenData);
        });

        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit dosen.
     */
    public function edit(Dosen $dosen): View
    {
        return view('dosen.edit', compact('dosen'));
    }

    /**
     * Memperbarui data dosen yang ada.
     */
    public function update(Request $request, Dosen $dosen): RedirectResponse
    {
        $request->validate([
            // Validasi Update (Ignore ID sendiri agar tidak error unique)
            'nidn' => 'required|max:20|unique:dosens,nidn,' . $dosen->id,
            'nik' => 'required|digits:16|unique:dosens,nik,' . $dosen->id,
            'nama_lengkap' => 'required|string|max:255',
            // Validasi email user (cek tabel users, ignore id user terkait)
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $dosen->user_id],
            
            'jenis_kelamin' => 'required|in:L,P',
            'status_kepegawaian' => 'required|string',
            
            'email_institusi' => 'nullable|email|max:255|unique:dosens,email_institusi,' . $dosen->id,
            'link_google_scholar' => 'nullable|url',
            'link_sinta' => 'nullable|url',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($request, $dosen) {
            // Bersihkan request dari field yang tidak masuk tabel dosen langsung
            $dataUpdate = $request->except(['email', 'password', 'password_confirmation', '_token', '_method', 'foto_profil']);
            
            // Casting checkbox boolean ke integer (1/0)
            $dataUpdate['is_keuangan'] = $request->has('is_keuangan') ? 1 : 0;

            // Handle Upload Foto Baru
            if ($request->hasFile('foto_profil')) {
                // Hapus foto lama jika ada
                if ($dosen->foto_profil && Storage::disk('public')->exists($dosen->foto_profil)) {
                    Storage::disk('public')->delete($dosen->foto_profil);
                }
                $dataUpdate['foto_profil'] = $request->file('foto_profil')->store('foto-profil-dosen', 'public');
            }

            // Update Tabel Dosen
            $dosen->update($dataUpdate);

            // Update Tabel User (Email & Password)
            if ($dosen->user) {
                $userData = ['name' => $request->nama_lengkap, 'email' => $request->email];
                
                // Hanya update password jika field diisi
                if ($request->filled('password')) {
                    $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
                    $userData['password'] = Hash::make($request->password);
                }
                
                $dosen->user->update($userData);
            }
        });

        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Menghapus data dosen dan user terkait.
     */
    public function destroy(Dosen $dosen): RedirectResponse
    {
        DB::transaction(function () use ($dosen) {
            // Hapus file foto
            if ($dosen->foto_profil && Storage::disk('public')->exists($dosen->foto_profil)) {
                Storage::disk('public')->delete($dosen->foto_profil);
            }

            // Hapus user (cascade delete biasanya akan menghapus dosen juga, 
            // tapi kita lakukan manual untuk keamanan logic)
            if ($dosen->user) {
                $dosen->user->delete(); // Ini akan memicu penghapusan dosen jika relasi cascade di set di DB
            } else {
                $dosen->delete();
            }
        });

        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil dihapus.');
    }

    /**
     * Ekspor data dosen ke Excel.
     */
    public function export() 
    {
        return Excel::download(new DosensExport, 'daftar-dosen.xlsx');
    }

    /**
     * Impor data dosen dari Excel.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        
        try {
            Excel::import(new DosensImport, $request->file('file'));
        } catch (ExcelValidationException $e) {
            // Tangkap error validasi spesifik per baris Excel
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . " (" . $failure->attribute() . "): " . implode(', ', $failure->errors());
            }
            return redirect()->route('admin.dosen.index')->with('error', 'Gagal mengimpor data: ' . implode(' | ', $errorMessages));
        
        } catch (\Exception $e) {
            // Error umum lainnya
            return redirect()->route('admin.dosen.index')->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil diimpor!');
    }
    
    /**
     * Download template Excel untuk impor.
     */
    public function downloadTemplate()
    {
        return Excel::download(new DosenImportTemplateExport, 'template-dosen.xlsx');
    }
}