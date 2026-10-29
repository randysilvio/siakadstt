<?php

namespace App\Console\Commands;

use App\Models\ProgramStudi;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\KelengkapanDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Notifikasi MINGGUAN ke bidang terkait (Keuangan, Kaprodi per prodi, Penjaminan Mutu) berisi data yang perlu
 * mereka lengkapi. Admin tidak dikirimi karena selalu melihat pengingat lengkap di ikon "!".
 */
class PengingatKelengkapanCommand extends Command
{
    protected $signature = 'siakad:pengingat-kelengkapan';
    protected $description = 'Kirim notifikasi mingguan kelengkapan data ke bidang terkait';

    public function handle(KelengkapanDataService $layanan): int
    {
        $butir = collect($layanan->ringkasan(true)['butir'])->whereNotNull('tujuan')->groupBy('tujuan');
        $total = 0;

        foreach ($butir as $tujuan => $daftar) {
            $penerima = $this->penerima($tujuan);
            if ($penerima->isEmpty()) {
                continue;
            }
            $poin = $daftar->map(fn ($b) => $b['judul'] . ' (' . $b['jumlah'] . ')')->implode('; ');
            Notification::send($penerima, new GeneralNotification(
                'Pengingat mingguan kelengkapan data',
                'Mohon ditindaklanjuti di SIAKAD: ' . $poin . '.',
                route('dashboard'),
                'bi-clipboard-check text-dark'
            ));
            $total += $penerima->count();
        }

        $this->info("Notifikasi kelengkapan data terkirim ke {$total} penerima.");
        return self::SUCCESS;
    }

    private function penerima(string $tujuan)
    {
        if (str_starts_with($tujuan, 'kaprodi:')) {
            $prodi = ProgramStudi::with('kaprodi.user')->find((int) substr($tujuan, 8));
            $user = optional(optional($prodi)->kaprodi)->user;
            return collect($user ? [$user] : []);
        }
        return User::whereHas('roles', fn ($q) => $q->where('name', $tujuan))->get();
    }
}
