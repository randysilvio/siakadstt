<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CekPeriodeKrsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Pastikan pengguna adalah mahasiswa dan memiliki data mahasiswa terkait
        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $mahasiswa = $user->mahasiswa;

            // ==================================================================
            // PERBAIKAN UTAMA: LOGIKA PENGUNCIAN KRS
            // Cek pertama: Jika status KRS sudah 'Disetujui', langsung blokir akses.
            // Ini adalah gerbang utama untuk mencegah perubahan.
            // ==================================================================
            if ($mahasiswa->status_krs === 'Disetujui') {
                return redirect()->route('dashboard')->with('warning', 'KRS Anda telah disetujui dan tidak dapat diubah lagi.');
            }
        }

        // Jika KRS belum disetujui atau pengguna bukan mahasiswa, lanjutkan ke pengecekan periode aktif
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        if (!$periodeAktif || !Carbon::now()->between(
                $periodeAktif->tanggal_mulai_krs,
                Carbon::parse($periodeAktif->tanggal_selesai_krs)->endOfDay()
            )) {
            // Redirect jika periode KRS tidak aktif
            return redirect()->route('dashboard')->with('error', 'Periode pengisian KRS tidak aktif.');
        }
        
        return $next($request);
    }
}
