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
        
        // --- LOGIKA PENENTUAN ROLE ---
        // Kita cek manual agar pasti
        $roleName = 'umum'; // Default

        if ($user->hasRole('mahasiswa')) {
            $roleName = 'mahasiswa';
            $user->load('mahasiswa.programStudi');
        } elseif ($user->hasRole('dosen')) {
            $roleName = 'dosen';
            $user->load('dosen');
        } elseif ($user->hasRole('pegawai') || $user->hasRole('admin')) {
            $roleName = 'pegawai'; // Anggap admin/pegawai sama untuk tampilan HP
        } else {
            // Ambil dari database jika ada role lain
            $roleName = $user->roles->first() ? $user->roles->first()->name : 'umum';
        }

        // Gabungkan data user dengan nama role yang sudah dipastikan
        $userData = $user->toArray();
        $userData['role'] = $roleName; 
        
        return $userData;
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // 2. FITUR ABSENSI (Pegawai/Dosen)
    Route::post('/absensi/check-in', [AbsensiController::class, 'checkIn']);
    Route::post('/absensi/check-out', [AbsensiController::class, 'checkOut']);
    Route::get('/absensi/riwayat', [AbsensiController::class, 'getHistory']);
    Route::get('/status-absensi', [AbsensiController::class, 'getStatusHariIni']);

    // 3. FITUR KEUANGAN (Mahasiswa)
    Route::get('/riwayat-pembayaran-mahasiswa', [AbsensiController::class, 'riwayatPembayaranMahasiswa']);

    // 4. FITUR AKADEMIK (Umum)
    Route::get('/kalender-akademik', [KalenderController::class, 'getKalenderUntukApi']);
    Route::get('/jadwal-hari-ini', [KalenderController::class, 'jadwalHariIni']); 

    // 5. FITUR NILAI / KHS (Mahasiswa)
    Route::get('/khs-mahasiswa', [AbsensiController::class, 'getKhsMahasiswa']);

    // 6. STATISTIK
    Route::get('/stats/mahasiswa-per-prodi', [DashboardController::class, 'mahasiswaPerProdi']);
});