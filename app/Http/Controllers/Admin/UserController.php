<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $roleId = $request->input('role_id');
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        $users = $query->paginate(15)->withQueryString();
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
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $request->validate($rules);

        // Admin tidak boleh mencabut peran admin dari akunnya sendiri (mencegah terkunci dari sistem).
        if ($user->id === Auth::id() && ($request->has('roles') || $request->boolean('roles_submitted'))) {
            $adminRoleId = Role::where('name', 'admin')->value('id');
            if ($adminRoleId && !in_array((string) $adminRoleId, array_map('strval', $request->input('roles', [])), true)) {
                return back()->withInput()->with('error', 'Anda tidak dapat mencabut peran Administrator dari akun Anda sendiri.');
            }
        }

        DB::transaction(function () use ($request, $user) {
            if ($request->filled('name')) {
                $user->name = $request->name;
            }
            if ($request->filled('email')) {
                $user->email = $request->email;
            }
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // Sinkronkan nama lengkap ke tabel profil jika ada
            if ($request->filled('name')) {
                if ($user->mahasiswa) {
                    $user->mahasiswa->update(['nama_lengkap' => $request->name]);
                }
                if ($user->dosen) {
                    $user->dosen->update(['nama_lengkap' => $request->name]);
                }
                if ($user->tendik) {
                    $user->tendik->update(['nama_lengkap' => $request->name]);
                }
            }

            // Peran hanya disinkronkan bila form peran benar-benar dikirim; update nama/email saja tidak boleh
            // menghapus seluruh peran pengguna.
            if ($request->has('roles') || $request->boolean('roles_submitted')) {
                $user->roles()->sync($request->input('roles', []));
            }
        });

        return redirect()->route('admin.user.index')->with('success', 'Data akun dan otoritas peran untuk ' . $user->name . ' berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Akun yang terikat riwayat akademik/keuangan tidak boleh dihapus (FK RESTRICT); nonaktifkan via profilnya.
        if ($user->mahasiswa && ($alasan = $user->mahasiswa->alasanTidakBisaDihapus())) {
            return back()->with('error', $alasan);
        }
        if ($user->dosen && ($alasan = $user->dosen->alasanTidakBisaDihapus())) {
            return back()->with('error', $alasan);
        }
        if ($user->camaba) {
            return back()->with('error', 'Akun ini milik pendaftar PMB. Hapus melalui menu PMB › Data Pendaftar agar dokumen dan tagihannya ikut dibersihkan.');
        }
        if ($user->pembayarans()->exists()) {
            return back()->with('error', 'Akun ini memiliki riwayat tagihan keuangan sehingga tidak dapat dihapus.');
        }

        DB::transaction(function () use ($user) {
            if ($user->tendik) {
                if ($user->tendik->foto_profil) {
                    Storage::disk('public')->delete($user->tendik->foto_profil);
                }
                $user->tendik->delete();
            }

            if ($user->dosen) {
                if ($user->dosen->foto_profil) {
                    Storage::disk('public')->delete($user->dosen->foto_profil);
                }
                $user->dosen->delete();
            }

            if ($user->mahasiswa) {
                if ($user->mahasiswa->foto_profil) {
                    Storage::disk('public')->delete($user->mahasiswa->foto_profil);
                }
                $user->mahasiswa->delete();
            }

            $user->roles()->detach();
            $user->delete();
        });

        return redirect()->route('admin.user.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function impersonate($id)
    {
        $userToImpersonate = User::findOrFail($id);

        if ($userToImpersonate->id === Auth::id() || $userToImpersonate->hasRole('admin')) {
            return back()->with('error', 'Anda tidak dapat menyamar menjadi diri sendiri atau sesama Admin.');
        }

        session()->put('impersonate_by', Auth::id());
        Auth::login($userToImpersonate);

        return redirect()->route('dashboard')->with('success', 'Anda sekarang login sebagai ' . $userToImpersonate->name);
    }

    public function stopImpersonate()
    {
        if (session()->has('impersonate_by')) {
            $adminId = session()->pull('impersonate_by');
            Auth::loginUsingId($adminId);
            session()->forget('impersonate_by');
            
            return redirect()->route('admin.user.index')->with('success', 'Penyamaran dihentikan. Anda telah kembali menjadi Admin.');
        }

        return redirect()->route('dashboard');
    }
}