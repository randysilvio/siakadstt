<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\KrsController;

// =========================================================================
// RUTE PUBLIK (Tanpa Token)
// =========================================================================
Route::post('/login', [AuthController::class, 'login']);

// =========================================================================
// RUTE TERPROTEKSI (Wajib Token / Login)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // 1. DATA USER & PROFIL
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $roleName = 'umum'; 

        if ($user->hasRole('mahasiswa')) {
            $roleName = 'mahasiswa';
            $user->load(['mahasiswa.programStudi', 'mahasiswa.mataKuliahs']);
        } elseif ($user->hasRole('dosen')) {
            $roleName = 'dosen';
            $user->load('dosen');
        } elseif ($user->hasRole('pegawai') || $user->hasRole('admin')) {
            $roleName = 'pegawai'; 
        } else {
            $roleName = $user->roles->first() ? $user->roles->first()->name : 'umum';
        }

        $userData = $user->toArray();
        $userData['role'] = $roleName; 
        
        return $userData;
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // 2. FITUR ABSENSI
    Route::post('/absensi/check-in', [AbsensiController::class, 'checkIn']);
    Route::post('/absensi/check-out', [AbsensiController::class, 'checkOut']);
    Route::get('/absensi/riwayat', [AbsensiController::class, 'getHistory']);
    Route::get('/status-absensi', [AbsensiController::class, 'getStatusHariIni']);

    // 3. FITUR KEUANGAN
    Route::get('/riwayat-pembayaran-mahasiswa', [AbsensiController::class, 'riwayatPembayaranMahasiswa']);

    // 4. FITUR AKADEMIK
    Route::get('/kalender-akademik', [KalenderController::class, 'getKalenderUntukApi']);
    Route::get('/jadwal-kuliah', [KalenderController::class, 'jadwalKuliahUser']); 

    // 5. FITUR NILAI / KHS
    Route::get('/khs-mahasiswa', [AbsensiController::class, 'getKhsMahasiswa']);

    // ==========================================================
    // 6. FITUR KRS & VALIDASI
    // ==========================================================
    Route::get('/krs/perlu-validasi', [KrsController::class, 'getPerluValidasiApi']);
    Route::post('/krs/validasi/{id}', [KrsController::class, 'validasiKrsApi']);
    
    // Rute Baru Pengisian KRS di HP
    Route::get('/krs/form', [KrsController::class, 'getFormKrsApi']);
    Route::post('/krs/submit', [KrsController::class, 'submitKrsApi']);

    // 7. STATISTIK
    Route::get('/stats/mahasiswa-per-prodi', [DashboardController::class, 'mahasiswaPerProdi']);

    // 8. FITUR NOTIFIKASI
    Route::get('/notifikasi', function (Request $request) {
        return response()->json($request->user()->notifications()->latest()->get());
    });

    Route::post('/notifikasi/{id}/read', function (Request $request, $id) {
        $notif = $request->user()->notifications()->where('id', $id)->first();
        if($notif) { $notif->markAsRead(); }
        return response()->json(['status' => 'success']);
    });
});