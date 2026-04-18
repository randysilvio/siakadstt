<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KrsValidated extends Notification
{
    use Queueable;

    protected $status;
    protected $catatan;

    public function __construct($status, $catatan)
    {
        $this->status = $status;
        $this->catatan = $catatan;
    }

    public function via($notifiable)
    {
        return ['database']; // Menyimpan notifikasi ke dalam tabel database
    }

    public function toArray($notifiable)
    {
        // Data ini yang akan dibaca oleh ikon lonceng di app.blade.php
        return [
            'title' => 'KRS Anda ' . $this->status,
            'message' => 'Status pengajuan KRS Anda saat ini adalah ' . $this->status . '. ' . ($this->catatan ? 'Catatan Kaprodi: ' . $this->catatan : ''),
            'url' => route('krs.index'), // Jika notifikasi diklik, arahkan ke halaman KRS
            'icon' => $this->status == 'Disetujui' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'
        ];
    }
}