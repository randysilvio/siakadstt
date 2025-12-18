<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\KalenderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- RUTE PUBLIK ---
Route::post('/login', [AuthController::class, 'login']);

// --- RUTE TERPROTEKSI (TOKEN) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // 1. DATA USER & CEK ROLE (PENTING UNTUK PEMISAHAN TAMPILAN)
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        
        // Load relasi agar data lengkap
        if ($user->hasRole('mahasiswa')) {
            $user->load('mahasiswa.programStudi');
        } elseif ($user->hasRole('dosen')) {
            $user->load('dosen');
        }
        
        // AMBIL NAMA ROLE DARI DATABASE
        // Ini kuncinya: Frontend butuh string ini untuk membedakan tampilan
        $roleName = $user->roles->first() ? $user->roles->first()->name : null;

        $userData = $user->toArray();
        $userData['role'] = $roleName; // Inject role ke respon JSON
        
        return $userData;
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // 2. ABSENSI (Dosen/Pegawai)
    Route::post('/absensi/check-in', [AbsensiController::class, 'checkIn']);
    Route::post('/absensi/check-out', [AbsensiController::class, 'checkOut']);
    Route::get('/absensi/riwayat', [AbsensiController::class, 'getHistory']);
    Route::get('/status-absensi', [AbsensiController::class, 'getStatusHariIni']);

    // 3. KEUANGAN (Mahasiswa)
    Route::get('/riwayat-pembayaran-mahasiswa', [AbsensiController::class, 'riwayatPembayaranMahasiswa']);

    // 4. AKADEMIK
    Route::get('/kalender-akademik', [KalenderController::class, 'getKalenderUntukApi']);
    Route::get('/jadwal-hari-ini', [KalenderController::class, 'jadwalHariIni']);
    Route::get('/khs-mahasiswa', [AbsensiController::class, 'getKhsMahasiswa']);
});