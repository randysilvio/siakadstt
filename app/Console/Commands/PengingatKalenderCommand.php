<?php

namespace App\Console\Commands;

use App\Models\KegiatanAkademik;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\KalenderPengendali;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Pengingat otomatis agenda kalender ke peran sasarannya: H-3 dan H-1 sebelum mulai,
 * serta H-1 sebelum acara pengendali ditutup (mis. "KRS ditutup besok"). Tiap pengingat hanya dikirim sekali.
 */
class PengingatKalenderCommand extends Command
{
    protected $signature = 'siakad:pengingat-kalender';
    protected $description = 'Kirim pengingat H-3/H-1 agenda Kalender Akademik ke peran sasaran';

    public function handle(): int
    {
        $hari = now()->startOfDay();
        $terkirim = 0;

        KegiatanAkademik::with('roles')
            ->whereDate('tanggal_selesai', '>=', $hari->toDateString())
            ->whereDate('tanggal_mulai', '<=', $hari->copy()->addDays(3)->toDateString())
            ->get()
            ->each(function (KegiatanAkademik $k) use ($hari, &$terkirim) {
                $mulai = Carbon::parse($k->tanggal_mulai)->startOfDay();
                $selesai = Carbon::parse($k->tanggal_selesai)->startOfDay();
                $sudah = $k->pengingat_terkirim ?? [];
                $kunci = null;
                $pesan = null;

                $keMulai = $hari->diffInDays($mulai, false);
                if (in_array($keMulai, [3, 1], true) && !in_array('h' . $keMulai, $sudah, true)) {
                    $kunci = 'h' . $keMulai;
                    $pesan = $k->judul_kegiatan . ' dimulai ' . ($keMulai === 1 ? 'besok' : '3 hari lagi') . ', ' . $mulai->translatedFormat('d F Y') . '.';
                } elseif (KalenderPengendali::pengendali($k) && $hari->diffInDays($selesai, false) === 1 && !in_array('tutup1', $sudah, true)) {
                    $kunci = 'tutup1';
                    $pesan = $k->judul_kegiatan . ' ditutup besok (' . $selesai->translatedFormat('d F Y') . ', 23.59 WIT).';
                }

                if (!$kunci) {
                    return;
                }
                $penerima = User::pemegangPeran($k->roles->pluck('id')->map(fn ($id) => (int) $id)->all());
                if ($penerima->isNotEmpty()) {
                    Notification::send($penerima, new GeneralNotification('Pengingat Agenda Akademik', $pesan, route('kalender.halaman'), 'bi-alarm text-dark'));
                    $terkirim += $penerima->count();
                }
                $k->forceFill(['pengingat_terkirim' => array_values(array_unique(array_merge($sudah, [$kunci])))])->saveQuietly();
            });

        $this->info("Pengingat kalender terkirim ke {$terkirim} penerima.");
        return self::SUCCESS;
    }
}
