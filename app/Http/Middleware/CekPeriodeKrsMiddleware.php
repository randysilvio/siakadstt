<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Carbon\Carbon;
class CekPeriodeKrsMiddleware
{
    public function handle(Request $request, Closure $next) {
        $periodeAktif = TahunAkademik::where('is_active', true)->first();
        if (!$periodeAktif || !Carbon::now()->between($periodeAktif->tanggal_mulai_krs, $periodeAktif->tanggal_selesai_krs)) {
            return redirect()->route('dashboard')->with('error', 'Periode pengisian KRS tidak aktif.');
        }
        return $next($request);
    }
}