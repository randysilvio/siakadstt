<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\JadwalKuliah; 
use App\Models\Krs;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // Di sinilah Anda bisa mendaftarkan tugas otomatis nanti,
        // seperti perintah 'app:buat-tagihan-semester' yang pernah kita diskusikan.

        // [OTOMASI NOTIFIKASI] Pengecekan Jadwal Otomatis setiap Jam 06:00 Pagi
        $schedule->call(function () {
            
            // 1. Dapatkan nama hari ini dalam bahasa Indonesia
            Carbon::setLocale('id');
            $hariIni = Carbon::now()->isoFormat('dddd'); // Menghasilkan: 'Senin', 'Selasa', dll.
            
            // 2. Ambil semua jadwal kuliah hari ini
            $jadwals = JadwalKuliah::where('hari', $hariIni)->with('mataKuliah')->get();
            
            foreach ($jadwals as $jadwal) {
                // 3. Cari Mahasiswa yang KRS-nya disetujui untuk mata kuliah ini
                $krsList = Krs::where('mata_kuliah_id', $jadwal->mata_kuliah_id)
                            ->where('status_krs', 'Disetujui')
                            ->with('mahasiswa.user')
                            ->get();
                            
                // 4. Kirim notifikasi ke tiap mahasiswa
                foreach ($krsList as $krs) {
                    if ($krs->mahasiswa && $krs->mahasiswa->user) {
                        $krs->mahasiswa->user->notify(new GeneralNotification(
                            'Jadwal Kuliah Hari Ini',
                            'Pengingat: Ada perkuliahan ' . $jadwal->mataKuliah->nama_mk . ' pada jam ' . $jadwal->jam_mulai,
                            route('dashboard'), // Jika diklik, arahkan ke dashboard
                            'bi-clock-fill text-warning'
                        ));
                    }
                }
            }
        })->dailyAt('06:00'); // Berjalan otomatis setiap pukul 06:00 pagi zona waktu server
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