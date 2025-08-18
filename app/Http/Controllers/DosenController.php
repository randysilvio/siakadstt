<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Exports\DosensExport;
use App\Imports\DosensImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException; // DIUBAH: Menggunakan exception dari Maatwebsite
use App\Exports\DosenImportTemplateExport;

class DosenController extends Controller
{
    public function index()
    {
        $dosens = Dosen::with('user')->latest()->paginate(10);
        return view('dosen.index', compact('dosens'));
    }

    public function create()
    {
        return view('dosen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nidn' => 'required|unique:dosens|max:20',
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'dosen',
            ]);

            Dosen::create([
                'user_id' => $user->id,
                'nidn' => $request->nidn,
                'nama_lengkap' => $request->nama_lengkap,
            ]);
        });

        return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil ditambahkan.');
    }

    public function edit(Dosen $dosen)
    {
        return view('dosen.edit', compact('dosen'));
    }

    public function update(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nidn' => 'required|max:20|unique:dosens,nidn,' . $dosen->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class . ',email,' . $dosen->user_id],
            'is_keuangan' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request, $dosen) {
            $dosen->update([
                'nidn' => $request->nidn,
                'nama_lengkap' => $request->nama_lengkap,
                'is_keuangan' => $request->has('is_keuangan'),
            ]);

            if ($dosen->user) {
                $dosen->user->update([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                ]);
            }
        });

        return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        DB::transaction(function () use ($dosen) {
            if ($dosen->user) {
                $dosen->user->delete();
            }
            $dosen->delete();
        });

        return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil dihapus.');
    }

    public function export() 
    {
        return Excel::download(new DosensExport, 'daftar-dosen.xlsx');
    }

    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        
        try {
            Excel::import(new DosensImport, $request->file('file'));
        } catch (ValidationException $e) { // DIUBAH: Tipe exception sudah spesifik
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            return redirect()->route('dosen.index')->with('error', 'Gagal mengimpor data: ' . implode(' | ', $errorMessages));
        
        } catch (\Exception $e) {
            return redirect()->route('dosen.index')->with('error', 'Terjadi kesalahan. Pastikan nama kolom di file Excel sudah benar dan coba lagi. Pesan: ' . $e->getMessage());
        }
        
        return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil diimpor!');
    }
    
    public function downloadTemplate()
    {
        return Excel::download(new DosenImportTemplateExport, 'template-dosen.xlsx');
    }
}