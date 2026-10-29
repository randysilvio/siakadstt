<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Push\AntrianPush;
use Illuminate\Notifications\Events\NotificationSent;

/**
 * Setiap notifikasi yang tersimpan ke database (lonceng) ikut dikirim sebagai push ke HP & browser
 * penerimanya. Berlaku untuk semua notifikasi yang sudah ada maupun yang ditambahkan nanti.
 */
class KirimPushNotifikasi
{
    public function __construct(private AntrianPush $antrian)
    {
    }

    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database' || !$event->notifiable instanceof User) {
            return;
        }
        $data = $event->response->data ?? null;
        if (!is_array($data)) {
            $data = method_exists($event->notification, 'toArray') ? (array) $event->notification->toArray($event->notifiable) : [];
        }
        $judul = trim((string) ($data['title'] ?? ''));
        $isi = trim((string) ($data['message'] ?? ''));
        if ($judul === '' && $isi === '') {
            return;
        }
        $tautan = $data['url'] ?? null;
        $this->antrian->tambah($event->notifiable->id, $judul ?: 'SIAKAD STT GPI Papua', $isi, [
            'jenis' => 'notifikasi',
            'id' => $event->response->id ?? $event->notification->id ?? null,
            'tautan' => ($tautan && $tautan !== '#') ? url($tautan) : url('/notifikasi'),
        ]);
    }
}
