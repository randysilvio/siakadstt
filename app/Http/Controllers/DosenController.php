<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dosens = Dosen::with('user')->get(); // Eager load user
        return view('dosen.index', compact('dosens'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dosen.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nidn' => 'required|unique:dosens|max:15',
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
                'is_keuangan' => $request->has('is_keuangan'),
            ]);
        });
        
        return redirect()->route('dosen.index')->with('success', 'Data Dosen dan akun login berhasil dibuat.');
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Dosen $dosen)
    {
        // Tidak digunakan
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dosen $dosen)
    {
        $dosen->load('user'); // Pastikan relasi user di-load
        return view('dosen.edit', compact('dosen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nidn' => 'required|max:15|unique:dosens,nidn,' . $dosen->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class . ',email,' . $dosen->user_id],
        ]);

        DB::transaction(function () use ($request, $dosen) {
            $dosenData = $request->only(['nidn', 'nama_lengkap']);
            $dosenData['is_keuangan'] = $request->has('is_keuangan');

            $dosen->update($dosenData);

            if ($dosen->user) {
                $dosen->user->update([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                ]);
            }
        });

        return redirect()->route('dosen.index')->with('success', 'Data Dosen berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dosen $dosen)
    {
        DB::transaction(function () use ($dosen) {
            if ($dosen->user) {
                $dosen->user->delete();
            }
            $dosen->delete();
        });
        return redirect()->route('dosen.index')->with('success', 'Data Dosen berhasil dihapus.');
    }
}
