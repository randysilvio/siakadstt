<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $url;
    public $icon;

    /**
     * Konstruktor untuk menerima data notifikasi secara dinamis.
     *
     * @param string $title Judul notifikasi
     * @param string $message Isi pesan
     * @param string $url Link tujuan saat diklik (default: '#')
     * @param string $icon Ikon Bootstrap Icons (default: info circle)
     */
    public function __construct($title, $message, $url = '#', $icon = 'bi-info-circle text-primary')
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->icon = $icon;
    }

    /**
     * Tentukan channel pengiriman (kita gunakan database).
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Format data yang disimpan di tabel 'notifications' kolom 'data'.
     */
    public function toArray($notifiable)
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
            'icon'    => $this->icon,
        ];
    }
}