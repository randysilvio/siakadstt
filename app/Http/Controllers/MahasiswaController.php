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
use Maatwebsite\Excel\Validators\ValidationException; // DIUBAH: Menggunakan exception dari Maatwebsite
use App\Exports\MahasiswaImportTemplateExport;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $program_studi_id = $request->input('program_studi_id');

        $mahasiswas = Mahasiswa::with('programStudi', 'user')
            ->when($search, function ($query, $search) {
                return $query->where('nama_lengkap', 'like', "%{$search}%")
                             ->orWhere('nim', 'like', "%{$search}%");
            })
            ->when($program_studi_id, function ($query, $program_studi_id) {
                return $query->where('program_studi_id', $program_studi_id);
            })
            ->latest()
            ->paginate(10);

        $program_studis = ProgramStudi::orderBy('nama_prodi')->get();

        return view('mahasiswa.index', [
            'mahasiswas' => $mahasiswas,
            'program_studis' => $program_studis
        ]);
    }

    public function create()
    {
        $program_studis = ProgramStudi::all();
        $dosens = Dosen::all();
        return view('mahasiswa.create', [
            'program_studis' => $program_studis,
            'dosens' => $dosens
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nim' => 'required|unique:mahasiswas|max:10',
            'nama_lengkap' => 'required|string|max:255',
            'program_studi_id' => 'required|exists:program_studis,id',
            'dosen_wali_id' => 'nullable|exists:dosens,id',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
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
                'role' => 'mahasiswa',
            ]);

            $mahasiswaData = $request->except(['email', 'password', 'password_confirmation', '_token']);
            $mahasiswaData['user_id'] = $user->id;
            $mahasiswaData['status_mahasiswa'] = 'Aktif';

            Mahasiswa::create($mahasiswaData);
        });

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa dan akun login berhasil dibuat!');
    }

    public function show(Mahasiswa $mahasiswa)
    {
        return redirect()->route('mahasiswa.edit', $mahasiswa);
    }

    public function edit(Mahasiswa $mahasiswa)
    {
        $program_studis = ProgramStudi::all();
        $dosens = Dosen::all();
        return view('mahasiswa.edit', [
            'mahasiswa' => $mahasiswa,
            'program_studis' => $program_studis,
            'dosens' => $dosens
        ]);
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $request->validate([
            'nim' => 'required|max:10|unique:mahasiswas,nim,' . $mahasiswa->id,
            'nama_lengkap' => 'required|string|max:255',
            'program_studi_id' => 'required|exists:program_studis,id',
            'dosen_wali_id' => 'nullable|exists:dosens,id',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class . ',email,' . $mahasiswa->user_id],
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

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui!');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        DB::transaction(function () use ($mahasiswa) {
            if($mahasiswa->user) {
                $mahasiswa->user->delete();
            }
            $mahasiswa->delete();
        });

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $program_studi_id = $request->input('program_studi_id');
        return Excel::download(new MahasiswasExport($search, $program_studi_id), 'mahasiswa.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new MahasiswasImport, $request->file('file'));
        } catch (ValidationException $e) { // DIUBAH: Tipe exception sudah spesifik
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            return redirect()->route('mahasiswa.index')->with('error', 'Gagal mengimpor data: ' . implode(' | ', $errorMessages));
        } catch (\Exception $e) {
            return redirect()->route('mahasiswa.index')->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
        
        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diimpor!');
    }

    public function downloadImportTemplate()
    {
        return Excel::download(new MahasiswaImportTemplateExport(), 'template-impor-mahasiswa.xlsx');
    }
}