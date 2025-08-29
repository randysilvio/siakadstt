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
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Middleware ini hanya berlaku untuk mahasiswa yang sedang login
        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $mahasiswa = $user->mahasiswa;

            // PERBAIKAN UTAMA: Pengecekan status KRS
            // Jika status KRS sudah 'Disetujui', langsung blokir akses.
            // Ini adalah gerbang utama untuk mencegah perubahan setelah divalidasi Kaprodi.
            if ($mahasiswa->status_krs === 'Disetujui') {
                return redirect()->route('dashboard')->with('warning', 'KRS Anda telah disetujui dan tidak dapat diubah lagi.');
            }
        }

        // Pengecekan periode KRS aktif berlaku untuk semua yang mencoba mengakses rute ini
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        // Cek jika tidak ada periode aktif atau jika tanggal saat ini di luar rentang KRS
        if (
            !$periodeAktif ||
            !Carbon::now()->between(
                $periodeAktif->tanggal_mulai_krs,
                Carbon::parse($periodeAktif->tanggal_selesai_krs)->endOfDay() // Hitung sampai akhir hari
            )
        ) {
            // Redirect jika periode KRS tidak aktif
            return redirect()->route('dashboard')->with('error', 'Periode pengisian KRS tidak aktif atau telah berakhir.');
        }

        // Lanjutkan ke request berikutnya jika semua kondisi terpenuhi
        return $next($request);
    }
}

