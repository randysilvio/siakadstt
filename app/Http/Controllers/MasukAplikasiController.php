<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\SesiWebController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/** Menukar tautan sekali pakai dari aplikasi seluler menjadi sesi login web. */
class MasukAplikasiController extends Controller
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $data = Cache::pull(SesiWebController::kunciCache($token));
        $user = $data ? User::find($data['user_id']) : null;

        if (!$user) {
            return redirect()->route('login')->with('error', 'Tautan masuk dari aplikasi sudah kedaluwarsa atau sudah dipakai. Silakan buka ulang dari aplikasi.');
        }

        // Selalu mulai sesi baru agar tidak tercampur akun/sesi sebelumnya di WebView yang sama
        $web = Auth::guard('web');
        if ($web->check()) {
            $web->logout();
        }
        $request->session()->invalidate();
        $web->login($user);
        $request->session()->regenerate();
        $request->session()->put('dari_aplikasi', true);

        return redirect(SesiWebController::tujuanAman($data['tujuan'] ?? null));
    }
}
