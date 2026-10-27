<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'Informasi profil berhasil diperbarui.');
    }
    
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto_profil' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();
        if ($user->mahasiswa) {
            if ($user->mahasiswa->foto_profil) {
                Storage::disk('public')->delete($user->mahasiswa->foto_profil);
            }
            $path = $request->file('foto_profil')->store('foto-profil', 'public');
            $user->mahasiswa->update(['foto_profil' => $path]);
        } elseif ($user->dosen) {
            if ($user->dosen->foto_profil) {
                Storage::disk('public')->delete($user->dosen->foto_profil);
            }
            $path = $request->file('foto_profil')->store('foto-profil-dosen', 'public');
            $user->dosen->update(['foto_profil' => $path]);
        } elseif ($user->tendik) {
            if ($user->tendik->foto_profil) {
                Storage::disk('public')->delete($user->tendik->foto_profil);
            }
            $path = $request->file('foto_profil')->store('foto-profil-tendik', 'public');
            $user->tendik->update(['foto_profil' => $path]);
        }

        return Redirect::route('profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Penghapusan akun mandiri dinonaktifkan. Akun SIAKAD terikat riwayat akademik & keuangan (KRS, nilai,
     * tagihan) yang wajib tetap utuh; penonaktifan akun dilakukan oleh Administrator.
     */
    public function destroy(Request $request): RedirectResponse
    {
        return Redirect::route('profile.edit')->with('error', 'Penghapusan akun secara mandiri tidak tersedia. Hubungi Administrator SIAKAD untuk menonaktifkan akun Anda.');
    }

    public function simpanFcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $user = auth()->user();
        $user->fcm_token = $request->token;
        $user->save();

        return response()->json([
            'message' => 'Token FCM berhasil disimpan.',
            'token_yang_disimpan' => $request->token
        ]);
    }
}