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

// --- RUTE OTENTIKASI (PUBLIK) ---
// Rute ini berada di luar grup agar tidak memerlukan token.
Route::post('/login', [AuthController::class, 'login']);


// =================================================================
// PERBAIKAN: Kembalikan grup middleware 'auth:sanctum' untuk
// melindungi semua rute di bawah ini.
// =================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Rute default Laravel untuk mengambil data user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute untuk logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- RUTE UNTUK FITUR ABSENSI ---
    Route::post('/absensi/check-in', [AbsensiController::class, 'checkIn']);
    Route::post('/absensi/check-out', [AbsensiController::class, 'checkOut']);
    Route::get('/absensi/riwayat', [AbsensiController::class, 'getHistory']);
    Route::get('/status-absensi', [AbsensiController::class, 'getStatusHariIni']);

    // --- RUTE BARU UNTUK KALENDER AKADEMIK ---
    Route::get('/kalender-akademik', [KalenderController::class, 'getKalenderUntukApi']);

    // Rute lama Anda untuk statistik (tetap dipertahankan)
    Route::get('/stats/mahasiswa-per-prodi', [DashboardController::class, 'mahasiswaPerProdi']);
});