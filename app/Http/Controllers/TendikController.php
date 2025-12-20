<?php

namespace App\Http\Controllers;

use App\Models\Tendik;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TendikController extends Controller
{
    /**
     * Menampilkan daftar Tendik.
     */
    public function index(Request $request): View
    {
        $query = Tendik::with('user.roles')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip_yayasan', 'like', "%{$search}%")
                  ->orWhere('unit_kerja', 'like', "%{$search}%");
            });
        }

        $tendiks = $query->paginate(10)->withQueryString();
        return view('tendik.index', compact('tendiks'));
    }

    public function create(): View
    {
        // Ambil peran selain 'mahasiswa' dan 'dosen' (karena mereka punya menu sendiri)
        $roles = Role::whereNotIn('name', ['mahasiswa', 'dosen', 'camaba'])->orderBy('display_name')->get();
        return view('tendik.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            // Akun
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            
            // Data Tendik
            'nip_yayasan' => 'nullable|unique:tendiks,nip_yayasan',
            'nitk' => 'nullable|unique:tendiks,nitk',
            'nik' => 'required|digits:16|unique:tendiks,nik',
            'jenis_kelamin' => 'required|in:L,P',
            'unit_kerja' => 'required|string',
            'jabatan' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Buat User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 2. Assign Role
            $user->roles()->attach($request->role_id);

            // 3. Buat Data Tendik
            $tendikData = $request->except(['email', 'password', 'password_confirmation', 'role_id', 'name', '_token', 'foto_profil']);
            $tendikData['user_id'] = $user->id;
            $tendikData['nama_lengkap'] = $request->name; // Sinkron nama

            if ($request->hasFile('foto_profil')) {
                $tendikData['foto_profil'] = $request->file('foto_profil')->store('foto-profil-tendik', 'public');
            }

            Tendik::create($tendikData);
        });

        return redirect()->route('admin.tendik.index')->with('success', 'Data Pegawai/Tendik berhasil ditambahkan.');
    }

    public function edit(Tendik $tendik): View
    {
        $roles = Role::whereNotIn('name', ['mahasiswa', 'dosen', 'camaba'])->orderBy('display_name')->get();
        // Ambil role user saat ini (asumsi 1 user 1 role utama untuk tendik)
        $currentRoleId = $tendik->user->roles->first()->id ?? null;
        
        return view('tendik.edit', compact('tendik', 'roles', 'currentRoleId'));
    }

    public function update(Request $request, Tendik $tendik): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $tendik->user_id,
            'role_id' => 'required|exists:roles,id',
            'nik' => 'required|digits:16|unique:tendiks,nik,' . $tendik->id,
            'nip_yayasan' => 'nullable|unique:tendiks,nip_yayasan,' . $tendik->id,
            'nitk' => 'nullable|unique:tendiks,nitk,' . $tendik->id,
            'unit_kerja' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $tendik) {
            // 1. Update Tendik
            $dataUpdate = $request->except(['email', 'password', 'password_confirmation', 'role_id', 'name', '_token', '_method', 'foto_profil']);
            $dataUpdate['nama_lengkap'] = $request->name;

            if ($request->hasFile('foto_profil')) {
                if ($tendik->foto_profil) Storage::disk('public')->delete($tendik->foto_profil);
                $dataUpdate['foto_profil'] = $request->file('foto_profil')->store('foto-profil-tendik', 'public');
            }

            $tendik->update($dataUpdate);

            // 2. Update User & Role
            $user = $tendik->user;
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            if ($request->filled('password')) {
                $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
                $user->update(['password' => Hash::make($request->password)]);
            }

            $user->roles()->sync([$request->role_id]);
        });

        return redirect()->route('admin.tendik.index')->with('success', 'Data Pegawai berhasil diperbarui.');
    }

    public function destroy(Tendik $tendik): RedirectResponse
    {
        DB::transaction(function () use ($tendik) {
            if ($tendik->foto_profil) Storage::disk('public')->delete($tendik->foto_profil);
            $tendik->user->delete(); // Delete User akan cascade delete Tendik
        });

        return redirect()->route('admin.tendik.index')->with('success', 'Data Pegawai berhasil dihapus.');
    }
}