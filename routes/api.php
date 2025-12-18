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
    // Endpoint ini dipanggil setiap kali aplikasi dibuka untuk cek role & nama
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        
        // Load data relasi profil (Mahasiswa/Dosen)
        // Gunakan 'hasRole' dari User.php Anda
        if ($user->hasRole('mahasiswa')) {
            $user->load('mahasiswa.programStudi');
        } elseif ($user->hasRole('dosen')) {
            $user->load('dosen');
        }
        
        // [PERBAIKAN UTAMA] 
        // Mengambil nama role secara manual dari relasi, bukan library Spatie.
        // Ini mencegah error "Call to undefined method getRoleNames()"
        $roleName = $user->roles->first() ? $user->roles->first()->name : null;

        // Gabungkan data user dengan nama role
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
    // Menampilkan riwayat pembayaran SPP/SKS
    Route::get('/riwayat-pembayaran-mahasiswa', [AbsensiController::class, 'riwayatPembayaranMahasiswa']);

    // 4. FITUR AKADEMIK (Umum)
    // Kalender Akademik & Jadwal Harian
    Route::get('/kalender-akademik', [KalenderController::class, 'getKalenderUntukApi']);
    Route::get('/jadwal-hari-ini', [KalenderController::class, 'jadwalHariIni']); 

    // 5. FITUR NILAI / KHS (Mahasiswa)
    // Menampilkan IPK, IPS, dan Nilai per semester
    Route::get('/khs-mahasiswa', [AbsensiController::class, 'getKhsMahasiswa']);

    // 6. STATISTIK (Opsional - Dashboard Admin)
    Route::get('/stats/mahasiswa-per-prodi', [DashboardController::class, 'mahasiswaPerProdi']);
});