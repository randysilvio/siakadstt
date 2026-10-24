<?php

namespace App\Notifications;

use App\Models\Mahasiswa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KrsValidated extends Notification
{
    use Queueable;

    protected string $status;
    protected ?string $catatan;

    /**
     * Mendukung pemanggilan new KrsValidated($mahasiswa, $status) maupun new KrsValidated($status, $catatan)
     */
    public function __construct($arg1, $arg2 = null, $arg3 = null)
    {
        if ($arg1 instanceof Mahasiswa) {
            $this->status  = (string) ($arg2 ?? $arg1->status_krs ?? 'Diperbarui');
            $this->catatan = $arg3 ?? $arg1->catatan_kaprodi;
        } else {
            $this->status  = (string) $arg1;
            $this->catatan = is_string($arg2) ? $arg2 : null;
        }
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'Status KRS: ' . $this->status,
            'message' => 'Status pengajuan KRS Anda saat ini adalah ' . strtoupper($this->status) . '.' . ($this->catatan ? ' Catatan Dosen Tutor / Kaprodi: ' . $this->catatan : ''),
            'url'     => route('krs.index'),
            'icon'    => $this->status === 'Disetujui' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger',
        ];
    }
}