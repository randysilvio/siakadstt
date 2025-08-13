<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Carbon\Carbon;

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
        $periodeAktif = TahunAkademik::where('is_active', true)->first();

        // PERBAIKAN: Tambahkan ->endOfDay() untuk memastikan perbandingan mencakup seluruh hari terakhir.
        // Ini mencegah mahasiswa diblokir pada hari terakhir pengisian KRS.
        if (!$periodeAktif || !Carbon::now()->between(
                $periodeAktif->tanggal_mulai_krs,
                Carbon::parse($periodeAktif->tanggal_selesai_krs)->endOfDay()
            )) {
            return redirect()->route('dashboard')->with('error', 'Periode pengisian KRS tidak aktif.');
        }
        
        return $next($request);
    }
}
