<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifikasinya = auth()->user()->notifications()->paginate(15);
        return view('notifikasi.index', compact('notifikasinya'));
    }

    public function tandaiDibaca($id)
    {
        $notifikasi = auth()->user()->notifications()->findOrFail($id);
        $notifikasi->markAsRead();
        
        if (isset($notifikasi->data['url']) && $notifikasi->data['url'] !== '#') {
            $url = $notifikasi->data['url'];
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            $targetHost = parse_url($url, PHP_URL_HOST);

            // [PERBAIKAN KEAMANAN FASE 6]: Pastikan hanya redirect ke internal aplikasi (Anti-Open Redirect)
            if (str_starts_with($url, '/') || ($targetHost && $targetHost === $appHost) || $targetHost === request()->getHost()) {
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