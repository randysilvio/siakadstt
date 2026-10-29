<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerangkatPush;
use App\Services\Push\PushFcm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Pendaftaran token push perangkat (aplikasi Android / browser). */
class PushTokenController extends Controller
{
    public function simpan(Request $request, PushFcm $fcm): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|max:512',
            'platform' => 'nullable|in:android,ios,web',
            'versi' => 'nullable|string|max:30',
        ]);
        PerangkatPush::daftarkan($request->user(), $data['token'], $data['platform'] ?? 'android', $data['versi'] ?? null);

        return response()->json([
            'message' => 'Perangkat terdaftar untuk notifikasi.',
            'push_aktif' => $fcm->aktif(),
        ]);
    }

    public function hapus(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => 'required|string|max:512']);
        PerangkatPush::where('token', $data['token'])->where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'Perangkat tidak lagi menerima notifikasi.']);
    }
}
