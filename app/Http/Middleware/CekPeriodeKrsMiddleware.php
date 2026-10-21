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
            !$periodeAktif->tanggal_mulai_krs ||
            !$periodeAktif->tanggal_selesai_krs ||
            !Carbon::now()->between(
                Carbon::parse($periodeAktif->tanggal_mulai_krs)->startOfDay(),
                Carbon::parse($periodeAktif->tanggal_selesai_krs)->endOfDay()
            )
        ) {
            $pesan = 'Periode pengisian KRS tidak aktif atau telah berakhir.';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => $pesan], 403);
            }
            return redirect()->route('dashboard')->with('error', $pesan);
        }

        // 2. Cek Gembok Status KRS Mahasiswa
        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $mahasiswa = $user->mahasiswa;

            $krsSemesterIni = $mahasiswa->mataKuliahs()
                                        ->wherePivot('tahun_akademik_id', $periodeAktif->id)
                                        ->exists();

            if ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni) {
                $pesan = 'KRS Anda telah disetujui dan tidak dapat diubah lagi.';
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['status' => 'error', 'message' => $pesan], 403);
                }
                return redirect()->route('dashboard')->with('warning', $pesan);
            }
        }

        return $next($request);
    }
}