<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Membersihkan otomatis (Self-Healing) data notifikasi lama di database
     * yang terlanjur menyimpan string JSON model Mahasiswa.
     */
    public static function sanitizeNotificationRecord($notif): void
    {
        $data = $notif->data;
        if (!is_array($data)) {
            return;
        }

        $title   = (string) ($data['title'] ?? 'Notifikasi Sistem');
        $message = (string) ($data['message'] ?? '');
        $changed = false;

        if (str_contains($title, '{"id":') || str_contains($message, '{"id":')) {
            $statusExtracted = 'Disetujui';
            if (preg_match('/"status_krs"\s*:\s*"([^"]+)"/', $title . ' ' . $message, $m)) {
                $statusExtracted = $m[1];
            } elseif (str_contains(strtolower($message), 'ditolak')) {
                $statusExtracted = 'Ditolak';
            }

            $catatanExtracted = null;
            if (preg_match('/"catatan_kaprodi"\s*:\s*"([^"]+)"/', $title . ' ' . $message, $mc)) {
                $catatanExtracted = $mc[1];
            }

            $data['title']   = 'Status KRS: ' . $statusExtracted;
            $data['message'] = 'Status pengajuan KRS Anda saat ini adalah ' . strtoupper($statusExtracted) . '.'
                . ($catatanExtracted ? ' Catatan Dosen Pembimbing / Kaprodi: ' . $catatanExtracted : '');
            $data['icon']    = $statusExtracted === 'Disetujui'
                ? 'bi-check-circle-fill text-success'
                : 'bi-x-circle-fill text-danger';
            $changed = true;
        }

        if ($changed) {
            $notif->data = $data;
            try {
                $notif->save();
            } catch (\Throwable $e) {
                // Abaikan jika gagal menulis ke DB, minimal tampilan di memori sudah bersih
            }
        }
    }

    public function index()
    {
        $user = auth()->user();

        // Bersihkan otomatis notifikasi milik user ini yang mengandung raw JSON
        foreach ($user->notifications()->latest()->limit(50)->get() as $item) {
            self::sanitizeNotificationRecord($item);
        }

        $notifikasinya = $user->notifications()->paginate(15);
        return view('notifikasi.index', compact('notifikasinya'));
    }

    public function tandaiDibaca($id)
    {
        $notifikasi = auth()->user()->notifications()->findOrFail($id);
        self::sanitizeNotificationRecord($notifikasi);
        $notifikasi->markAsRead();
        
        if (isset($notifikasi->data['url']) && $notifikasi->data['url'] !== '#') {
            $url = $notifikasi->data['url'];
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            $targetHost = parse_url($url, PHP_URL_HOST);

            // Pastikan hanya redirect ke internal aplikasi (Anti-Open Redirect)
            if ((str_starts_with($url, '/') && !str_starts_with($url, '//') && !str_starts_with($url, '/\\')) || ($targetHost && $targetHost === $appHost) || $targetHost === request()->getHost()) {
                return redirect($url);
            }
        }
        
        return back();
    }

    public function tandaiSemuaDibaca()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua pemberitahuan telah ditandai sudah dibaca.');
    }
}