<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Menampilkan halaman semua riwayat notifikasi
    public function index()
    {
        // Mengambil seluruh notifikasi dengan pagination (15 per halaman)
        $notifikasinya = auth()->user()->notifications()->paginate(15);
        return view('notifikasi.index', compact('notifikasinya'));
    }

    // Menandai satu notifikasi telah dibaca saat diklik
    public function tandaiDibaca($id)
    {
        $notifikasi = auth()->user()->notifications()->findOrFail($id);
        $notifikasi->markAsRead();
        
        // Arahkan ke URL tujuan jika ada di dalam data notifikasi
        if (isset($notifikasi->data['url']) && $notifikasi->data['url'] !== '#') {
            return redirect($notifikasi->data['url']);
        }
        
        return back();
    }

    // Menandai semua notifikasi telah dibaca sekaligus
    public function tandaiSemuaDibaca()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua pemberitahuan telah ditandai sudah dibaca.');
    }
}