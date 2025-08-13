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
     * Menampilkan daftar semua dosen.
     */
    public function index()
    {
        $dosens = Dosen::with('user')->latest()->paginate(10);
        return view('dosen.index', compact('dosens'));
    }

    /**
     * Menampilkan form untuk membuat dosen baru.
     */
    public function create()
    {
        return view('dosen.create');
    }

    /**
     * Menyimpan dosen baru ke database.
     */
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

    /**
     * Menampilkan form untuk mengedit data dosen.
     */
    public function edit(Dosen $dosen)
    {
        return view('dosen.edit', compact('dosen'));
    }

    /**
     * Memperbarui data dosen di database.
     */
    public function update(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nidn' => 'required|max:20|unique:dosens,nidn,' . $dosen->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class . ',email,' . $dosen->user_id],
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
        
        // Perbaikan: Hapus baris ini agar tidak ada notifikasi ganda
        // return redirect()->route('dosen.edit', $dosen)->with('success', 'Data dosen berhasil diperbarui.');

        return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Menghapus data dosen dari database.
     */
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
}