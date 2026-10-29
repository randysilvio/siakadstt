<?php

namespace App\Listeners;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Auth\Events\Login;

/** Mencatat waktu login terakhir & log aktivitas masuk (web). Penyamaran admin dicatat atas nama admin. */
class CatatLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (!$user instanceof User) {
            return;
        }

        $penyamar = session()->get('impersonate_by');
        if ($penyamar && (int) $penyamar !== (int) $user->id) {
            LogAktivitas::catat('login', 'Masuk sebagai ' . $user->name . ' (mode penyamaran admin)', $user, null, (int) $penyamar);
            return;
        }
        if (request()->routeIs('impersonate.stop')) {
            LogAktivitas::catat('login', 'Mengakhiri penyamaran, kembali sebagai admin', $user, null, $user->id);
            return;
        }

        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        LogAktivitas::catat('login', 'Masuk ke SIAKAD web', $user, null, $user->id);
    }
}
