<?php

namespace App\Notifications;

use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KrsValidated extends Notification
{
    use Queueable;

    protected string $status;
    protected ?string $catatan;

    /**
     * Mendukung pemanggilan new KrsValidated($mahasiswa, $status, $catatan)
     * maupun new KrsValidated($status, $catatan) tanpa pernah membocorkan objek JSON Mahasiswa.
     */
    public function __construct($arg1, $arg2 = null, $arg3 = null)
    {
        if ($arg1 instanceof Mahasiswa) {
            $this->status  = is_string($arg2) && $arg2 !== '' ? $arg2 : ($arg1->status_krs ?? 'Diperbarui');
            $this->catatan = is_string($arg3) ? $arg3 : $arg1->catatan_kaprodi;
        } elseif (is_string($arg1) && str_starts_with(trim($arg1), '{')) {
            $decoded = json_decode($arg1, true);
            $this->status  = is_string($arg2) && in_array($arg2, ['Disetujui', 'Ditolak', 'Menunggu Persetujuan'])
                ? $arg2
                : ($decoded['status_krs'] ?? 'Disetujui');
            $this->catatan = is_string($arg3) ? $arg3 : ($decoded['catatan_kaprodi'] ?? null);
        } else {
            $this->status  = is_scalar($arg1) ? (string) $arg1 : 'Diperbarui';
            $this->catatan = is_string($arg2) ? $arg2 : null;
        }

        if ($this->catatan && in_array(strtolower(trim($this->catatan)), ['disetujui', 'ditolak'])) {
            $this->catatan = null;
        }
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $istilahPembimbing = Pengaturan::get('istilah_pembimbing', 'Dosen Tutor');
        $pesanCatatan = !empty($this->catatan)
            ? " Catatan {$istilahPembimbing} / Kaprodi: {$this->catatan}"
            : '';

        return [
            'title'   => 'Status KRS: ' . $this->status,
            'message' => 'Status pengajuan KRS Anda saat ini adalah ' . strtoupper($this->status) . '.' . $pesanCatatan,
            'url'     => route('krs.index'),
            'icon'    => $this->status === 'Disetujui' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger',
        ];
    }
}