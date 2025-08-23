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
     * Menampilkan daftar semua pengguna.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.user.index', compact('users'));
    }

    /**
     * Menampilkan formulir untuk mengedit peran seorang pengguna.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('admin.user.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Memperbarui peran seorang pengguna.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        // Menggunakan sync untuk memperbarui peran
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('user.index')->with('success', 'Peran untuk pengguna ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna.
     * PERINGATAN: Ini akan menghapus data user beserta relasinya (dosen/mahasiswa).
     */
    public function destroy(User $user)
    {
        // Pengecekan keamanan dasar: jangan biarkan user menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($user) {
            // Hapus data terkait (dosen/mahasiswa) terlebih dahulu jika ada
            if ($user->dosen) {
                $user->dosen->delete();
            }
            if ($user->mahasiswa) {
                $user->mahasiswa->delete();
            }
            // Hapus relasi peran dan kemudian hapus user
            $user->roles()->detach();
            $user->delete();
        });

        return redirect()->route('user.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
