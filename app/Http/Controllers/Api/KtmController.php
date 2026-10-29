<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Ktm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Data E-KTM digital untuk aplikasi seluler (desain kartu tetap, lihat App\Support\Ktm). */
class KtmController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $mahasiswa = $request->user()->mahasiswa;
        if (!$mahasiswa) {
            return response()->json(['message' => 'KTM hanya tersedia untuk akun mahasiswa.'], 403);
        }

        return response()->json(Ktm::data($mahasiswa));
    }
}
