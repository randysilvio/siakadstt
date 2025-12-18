<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\KalenderController;

// --- RUTE PUBLIK ---
Route::post('/login', [AuthController::class, 'login']);

// --- RUTE TERPROTEKSI (TOKEN) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // 1. User & Profil
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        if ($user->hasRole('mahasiswa')) {
            $user->load('mahasiswa.programStudi');
        } elseif ($user->hasRole('dosen')) {
            $user->load('dosen');
        }
        $userData = $user->toArray();
        $userData['role'] = $user->getRoleNames()->first(); 
        return $userData;
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // 2. Absensi
    Route::post('/absensi/check-in', [AbsensiController::class, 'checkIn']);
    Route::post('/absensi/check-out', [AbsensiController::class, 'checkOut']);
    Route::get('/absensi/riwayat', [AbsensiController::class, 'getHistory']);
    Route::get('/status-absensi', [AbsensiController::class, 'getStatusHariIni']);

    // 3. Keuangan (Mahasiswa)
    Route::get('/riwayat-pembayaran-mahasiswa', [AbsensiController::class, 'riwayatPembayaranMahasiswa']);

    // 4. Akademik (Kalender, Jadwal, KHS)
    Route::get('/kalender-akademik', [KalenderController::class, 'getKalenderUntukApi']);
    Route::get('/jadwal-hari-ini', [KalenderController::class, 'jadwalHariIni']);
    Route::get('/khs-mahasiswa', [AbsensiController::class, 'getKhsMahasiswa']); // <-- Endpoint KHS
});