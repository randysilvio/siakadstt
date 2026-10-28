<?php

namespace App\Http\Middleware;

use App\Models\Pengaturan;
use App\Models\TahunAkademik;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CekPeriodeKrsMiddleware
{
    /**
     * Keadaan periode pengisian KRS saat ini (dipakai middleware ini dan API seluler).
     *
     * @return array{buka: bool, saklar: string, mulai: ?string, selesai: ?string, pesan: ?string}
     */
    public static function keadaan(?TahunAkademik $periodeAktif = null): array
    {
        $periodeAktif ??= TahunAkademik::where('is_active', true)->first();
        $saklar = strtolower((string) Pengaturan::get('status_krs', 'buka'));
        $mulai = $periodeAktif?->tanggal_mulai_krs;
        $selesai = $periodeAktif?->tanggal_selesai_krs;

        $diLuarTanggal = false;
        if ($mulai && $selesai) {
            $diLuarTanggal = !Carbon::now()->between(Carbon::parse($mulai)->startOfDay(), Carbon::parse($selesai)->endOfDay());
        }

        $pesan = match (true) {
            !$periodeAktif       => 'Belum ada Tahun Akademik yang aktif.',
            $saklar === 'tutup'  => 'Periode pengisian KRS saat ini sedang ditutup oleh Administrator.',
            $diLuarTanggal       => 'Periode pengisian KRS tidak aktif atau telah berakhir.',
            default              => null,
        };

        return [
            'buka'    => $pesan === null,
            'saklar'  => $saklar,
            'mulai'   => $mulai ? Carbon::parse($mulai)->toDateString() : null,
            'selesai' => $selesai ? Carbon::parse($selesai)->toDateString() : null,
            'pesan'   => $pesan,
        ];
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        // 0. Cek Saklar Global Status KRS di Pengaturan Sistem
        $statusKrsGlobal = strtolower((string) Pengaturan::get('status_krs', 'buka'));
        if ($statusKrsGlobal === 'tutup' && !$request->isMethod('GET')) {
            $pesan = 'Periode pengisian KRS saat ini sedang ditutup oleh Administrator.';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => $pesan], 403);
            }
            return redirect()->route('dashboard')->with('error', $pesan);
        }

        // 1. Cek Rentang Tanggal KRS pada Tahun Akademik Aktif
        if (!$periodeAktif) {
            $pesan = 'Belum ada Tahun Akademik yang aktif.';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => $pesan], 403);
            }
            return redirect()->route('dashboard')->with('error', $pesan);
        }

        $isOutsideDate = false;
        if ($periodeAktif->tanggal_mulai_krs && $periodeAktif->tanggal_selesai_krs) {
            $isOutsideDate = !Carbon::now()->between(
                Carbon::parse($periodeAktif->tanggal_mulai_krs)->startOfDay(),
                Carbon::parse($periodeAktif->tanggal_selesai_krs)->endOfDay()
            );
        }

        // Jika di luar tanggal KRS (atau saklar ditutup) dan mahasiswa belum punya KRS yang disetujui, tolak akses
        if (($isOutsideDate || $statusKrsGlobal === 'tutup') && !$request->isMethod('GET')) {
            $pesan = 'Periode pengisian KRS tidak aktif atau telah berakhir.';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => $pesan], 403);
            }
            return redirect()->route('dashboard')->with('error', $pesan);
        }

        // 2. Cek Gembok Status KRS Mahasiswa (Hanya blokir aksi perubahan POST/DELETE jika sudah Disetujui, biarkan GET tetap terbuka untuk cetak KRS)
        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $mahasiswa = $user->mahasiswa;

            $krsSemesterIni = $mahasiswa->mataKuliahs()
                ->wherePivot('tahun_akademik_id', $periodeAktif->id)
                ->exists();

            if ($mahasiswa->status_krs === 'Disetujui' && $krsSemesterIni && !$request->isMethod('GET')) {
                $pesan = 'KRS Anda telah disetujui dan dikunci.';
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['status' => 'error', 'message' => $pesan], 403);
                }
                return redirect()->route('krs.index')->with('error', $pesan);
            }

            if (($isOutsideDate || $statusKrsGlobal === 'tutup') && !$krsSemesterIni) {
                $pesan = 'Periode pengisian KRS tidak aktif atau telah berakhir.';
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['status' => 'error', 'message' => $pesan], 403);
                }
                return redirect()->route('dashboard')->with('error', $pesan);
            }
        }

        return $next($request);
    }
}