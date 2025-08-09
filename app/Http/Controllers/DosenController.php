<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\User; // <-- Import User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- Import DB
use Illuminate\Support\Facades\Hash; // <-- Import Hash
use Illuminate\Validation\Rules; // <-- Import Rules

class DosenController extends Controller
{
    public function index()
    {
        $dosens = Dosen::with('user')->get(); // Eager load user
        return view('dosen.index', compact('dosens'));
    }

    public function create()
    {
        return view('dosen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nidn' => 'required|unique:dosens|max:15',
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
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
        
        return redirect()->route('dosen.index')->with('success', 'Data Dosen dan akun login berhasil dibuat.');
    }
    
    public function edit(Dosen $dosen)
    {
        $dosen->load('user'); // Pastikan relasi user di-load
        return view('dosen.edit', compact('dosen'));
    }

    public function update(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nidn' => 'required|max:15|unique:dosens,nidn,' . $dosen->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class . ',email,' . $dosen->user_id],
        ]);

        DB::transaction(function () use ($request, $dosen) {
            $dosen->update([
                'nidn' => $request->nidn,
                'nama_lengkap' => $request->nama_lengkap,
            ]);
            $dosen->user->update([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
            ]);
        });

        return redirect()->route('dosen.index')->with('success', 'Data Dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        DB::transaction(function () use ($dosen) {
            $dosen->user->delete();
            $dosen->delete();
        });
        return redirect()->route('dosen.index')->with('success', 'Data Dosen berhasil dihapus.');
    }
}