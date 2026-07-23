<?php

namespace App\Http\Middleware;

use App\Models\TahunAkademik;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CekPeriodeKrsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        // 1. Cek Gembok Waktu/Periode KRS
        if (
            !$periodeAktif ||
            !Carbon::now()->between(
                $periodeAktif->tanggal_mulai_krs,
                Carbon::parse($periodeAktif->tanggal_selesai_krs)->endOfDay()
            )
        ) {
            return redirect()->route('dashboard')->with('error', 'Periode pengisian KRS tidak aktif atau telah berakhir.');
        }

        // 2. Cek Gembok Status KRS Mahasiswa
        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $mahasiswa = $user->mahasiswa;

            // Pastikan kita HANYA memblokir jika KRS sudah disetujui PADA SEMESTER YANG SEDANG AKTIF INI.
            // Kita cek apakah mahasiswa sudah punya entri KRS di tabel pivot untuk periode aktif ini.
            $krsSemesterIni = $mahasiswa->mataKuliahs()
                                        ->wherePivot('tahun_akademik_id', $periodeAktif->id)
                                        ->exists();

            // Jika statusnya Disetujui DAN dia memang sudah punya KRS di semester ini, blokir.
            if ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni) {
                return redirect()->route('dashboard')->with('warning', 'KRS Anda telah disetujui dan tidak dapat diubah lagi.');
            }
        }

        return $next($request);
    }
}