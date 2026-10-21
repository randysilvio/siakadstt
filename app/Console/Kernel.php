<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Jadwal; 
use App\Models\TahunAkademik;
use App\Models\Mahasiswa;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // [PERBAIKAN BUG FATAL FASE 6]: Menggunakan model Jadwal & TahunAkademik aktif yang sesuai dengan skema DB
        $schedule->call(function () {
            $periodeAktif = TahunAkademik::where('is_active', true)->first();
            if (!$periodeAktif) {
                return;
            }

            Carbon::setLocale('id');
            $hariIni = Carbon::now()->isoFormat('dddd'); // 'Senin', 'Selasa', dst.
            
            $jadwals = Jadwal::where('hari', $hariIni)->with('mataKuliah')->get();
            
            foreach ($jadwals as $jadwal) {
                if (!$jadwal->mataKuliah) {
                    continue;
                }

                $mahasiswas = Mahasiswa::where('status_krs', 'Disetujui')
                    ->whereHas('mataKuliahs', function ($q) use ($jadwal, $periodeAktif) {
                        $q->where('mata_kuliahs.id', $jadwal->mata_kuliah_id)
                          ->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeAktif->id);
                    })
                    ->with('user')
                    ->get();
                            
                foreach ($mahasiswas as $mhs) {
                    if ($mhs->user) {
                        $mhs->user->notify(new GeneralNotification(
                            'Jadwal Kuliah Hari Ini',
                            'Pengingat: Ada perkuliahan ' . $jadwal->mataKuliah->nama_mk . ' pada jam ' . substr($jadwal->jam_mulai, 0, 5) . ' WIT',
                            route('dashboard'),
                            'bi-clock-fill text-warning'
                        ));
                    }
                }
            }
        })->dailyAt('06:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}