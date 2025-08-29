<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
// --- PENAMBAHAN CONTROLLER BARU UNTUK API ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// --- RUTE OTENTIKASI UNTUK APLIKASI MOBILE ---
Route::post('/login', [AuthController::class, 'login']);

// Grup rute yang membutuhkan otentikasi Sanctum
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
    Route::get('/absensi/status-hari-ini', [AbsensiController::class, 'getStatusHariIni']);

    // Rute lama Anda untuk statistik (tetap dipertahankan)
    Route::get('/stats/mahasiswa-per-prodi', [DashboardController::class, 'mahasiswaPerProdi']);
});