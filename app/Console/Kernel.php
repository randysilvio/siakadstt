<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Jadwal; 
use App\Models\TahunAkademik;
use App\Models\Mahasiswa;
use App\Models\KrsPersetujuan;
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
        $schedule->call(fn () => $this->kirimPengingatKuliah())->dailyAt('06:00');

        // Detak penjadwal: dashboard admin memperingatkan bila cron server tidak berjalan
        $schedule->call(fn () => \Illuminate\Support\Facades\Cache::forever('siakad-penjadwal-terakhir', now()->timestamp))->hourly();

        // Kalender akademik: pengingat H-3 / H-1 & "ditutup besok" untuk acara pengendali
        $schedule->command('siakad:pengingat-kalender')->dailyAt('07:00');

        // Pengingat kelengkapan data ke bidang terkait — seminggu sekali (Senin pagi)
        $schedule->command('siakad:pengingat-kelengkapan')->weeklyOn(1, '07:30');

        // Log aktivitas disimpan 1 tahun
        $schedule->call(fn () => \App\Models\LogAktivitas::where('created_at', '<', now()->subYear())->delete())->weekly();
    }

    /** Pengingat jadwal kuliah harian (dipanggil scheduler pukul 06:00). */
    public function kirimPengingatKuliah(): void
    {
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

            // Hanya mahasiswa aktif yang KRS semester ini DISETUJUI dan mengambil ROMBEL jadwal ini
            // (mahasiswa kelas B tidak ikut menerima pengingat jadwal kelas A)
            $rombel = strtoupper(trim((string) ($jadwal->nama_kelas ?: 'A')));
            $mahasiswas = Mahasiswa::where('status_mahasiswa', 'Aktif')
                ->whereIn('id', KrsPersetujuan::idsDisetujui((int) $periodeAktif->id))
                ->whereHas('mataKuliahs', function ($q) use ($jadwal, $periodeAktif, $rombel) {
                    $q->where('mata_kuliahs.id', $jadwal->mata_kuliah_id)
                      ->where('mahasiswa_mata_kuliah.tahun_akademik_id', $periodeAktif->id)
                      ->whereRaw("UPPER(COALESCE(NULLIF(TRIM(mahasiswa_mata_kuliah.nama_kelas), ''), 'A')) = ?", [$rombel]);
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