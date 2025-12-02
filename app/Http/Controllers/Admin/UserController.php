<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna dengan Smart Filter.
     */
    public function index(Request $request)
    {
        $query = User::with('roles')->latest();

        // 1. Filter Pencarian Teks (Nama, Email)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 2. [BARU] Filter Peran (Role)
        if ($request->filled('role_id')) {
            $roleId = $request->input('role_id');
            // Mencari user yang memiliki role tertentu melalui relasi many-to-many
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        $users = $query->paginate(15)->withQueryString();
        
        // Data untuk Dropdown Filter
        $roles = Role::orderBy('display_name')->get();

        return view('admin.user.index', compact('users', 'roles'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('admin.user.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.user.index')->with('success', 'Peran untuk pengguna ' . $user->name . ' berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($user) {
            if ($user->dosen) {
                $user->dosen->delete();
            }
            if ($user->mahasiswa) {
                $user->mahasiswa->delete();
            }
            $user->roles()->detach();
            $user->delete();
        });

        return redirect()->route('admin.user.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}