<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $data_mahasiswa = Mahasiswa::with('programStudi', 'user')
                                    ->where(function ($query) use ($search) {
                                        $query->where('nama_lengkap', 'like', "%{$search}%")
                                              ->orWhere('nim', 'like', "%{$search}%");
                                    })
                                    ->paginate(5);
        return view('mahasiswa.index', ['mahasiswas' => $data_mahasiswa]);
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
            'nama_lengkap' => 'required',
            'program_studi_id' => 'required|exists:program_studis,id',
            'dosen_wali_id' => 'nullable|exists:dosens,id',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'mahasiswa',
            ]);

            Mahasiswa::create([
                'user_id' => $user->id,
                'nim' => $request->nim,
                'nama_lengkap' => $request->nama_lengkap,
                'program_studi_id' => $request->program_studi_id,
                'dosen_wali_id' => $request->dosen_wali_id,
            ]);
        });

        return redirect('/mahasiswa')->with('success', 'Data mahasiswa dan akun login berhasil dibuat!');
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
            'nama_lengkap' => 'required',
            'program_studi_id' => 'required|exists:program_studis,id',
            'dosen_wali_id' => 'nullable|exists:dosens,id',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class . ',email,' . $mahasiswa->user_id],
        ]);

        DB::transaction(function () use ($request, $mahasiswa) {
            $mahasiswa->update([
                'nim' => $request->nim,
                'nama_lengkap' => $request->nama_lengkap,
                'program_studi_id' => $request->program_studi_id,
                'dosen_wali_id' => $request->dosen_wali_id,
            ]);

            if ($mahasiswa->user) {
                $mahasiswa->user->update([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                ]);
            }
        });

        return redirect('/mahasiswa')->with('success', 'Data mahasiswa berhasil diperbarui!');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        DB::transaction(function () use ($mahasiswa) {
            if($mahasiswa->user) {
                $mahasiswa->user->delete();
            }
            $mahasiswa->delete();
        });

        return redirect('/mahasiswa')->with('success', 'Data mahasiswa berhasil dihapus!');
    }
}