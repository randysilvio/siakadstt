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
        }

        return Redirect::route('profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    // ==========================================================
    // FUNGSI PENERIMA TOKEN DARI BROWSER FIREBASE
    // ==========================================================
    public function simpanFcmToken(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'token' => 'required|string'
        ]);

        // 2. Gunakan cara simpan manual (Mencegah masalah fillable / mass assignment yang menyebabkan Null)
        $user = auth()->user();
        $user->fcm_token = $request->token;
        $user->save();

        // 3. Kembalikan response sukses
        return response()->json([
            'message' => 'Token FCM berhasil disimpan.',
            'token_yang_disimpan' => $request->token
        ]);
    }
}