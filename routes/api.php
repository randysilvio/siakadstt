<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\Api\DokumenController;
use App\Http\Controllers\Api\KrsSirkulasiController;
use App\Http\Controllers\Api\SesiWebController;
use App\Http\Controllers\Api\VerumController as VerumApiController;

// =========================================================================
// RUTE PUBLIK (Tanpa Token)
// =========================================================================
Route::post('/login', [AuthController::class, 'login']);

// Versi minimum aplikasi seluler (versionCode Android) & tautan unduhan APK terbaru — diatur di config/mobile.php / .env
Route::get('/check-version', function () {
    return response()->json([
        'min_version'  => config('mobile.min_version'),
        'download_url' => config('mobile.download_url'),
    ]);
});

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
        } elseif ($user->tendik || $user->hasRole(['admin', 'keuangan', 'pustakawan', 'administrasi_umum', 'penjaminan_mutu', 'rektorat'])) {
            // Tidak ada role bernama 'pegawai'; aplikasi mobile memakai label ini untuk staf/tendik (fitur absensi)
            $roleName = 'pegawai'; 
        } else {
            $roleName = $user->roles->first() ? $user->roles->first()->name : 'umum';
        }

        $userData = $user->toArray();
        $userData['role'] = $roleName;
        $userData['roles'] = $user->roles->pluck('name')->values();
        $userData['is_kaprodi'] = $user->isKaprodi();
        $userData['is_keuangan'] = $user->hasRole('keuangan');
        
        return $userData;
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // 2. FITUR ABSENSI PEGAWAI & DOSEN
    Route::post('/absensi/check-in', [AbsensiController::class, 'checkIn']);
    Route::post('/absensi/check-out', [AbsensiController::class, 'checkOut']);
    Route::get('/absensi/riwayat', [AbsensiController::class, 'getHistory']);
    Route::get('/status-absensi', [AbsensiController::class, 'getStatusHariIni']);

    // 3. FITUR KEUANGAN MAHASISWA
    Route::get('/riwayat-pembayaran-mahasiswa', [AbsensiController::class, 'riwayatPembayaranMahasiswa']);
    Route::post('/pembayaran/{pembayaran}/bukti', [AbsensiController::class, 'unggahBuktiPembayaran'])->middleware('throttle:10,1');

    // 4. FITUR AKADEMIK
    Route::get('/kalender-akademik', [KalenderController::class, 'getKalenderUntukApi']);
    Route::get('/jadwal-kuliah', [KalenderController::class, 'jadwalKuliahUser']); 

    // 5. FITUR NILAI / KHS
    Route::get('/khs-mahasiswa', [AbsensiController::class, 'getKhsMahasiswa']);

    // 6. FITUR KRS & VALIDASI (Dosen Wali / Kaprodi / Admin) — aturan sama dengan web (AcademicRuleService)
    Route::middleware('role:dosen,kaprodi,admin')->group(function () {
        Route::get('/krs/perlu-validasi', [KrsController::class, 'getPerluValidasiApi']);
        Route::get('/krs/mahasiswa', [KrsSirkulasiController::class, 'daftar']);
        Route::get('/krs/validasi/{id}', [KrsSirkulasiController::class, 'rincian'])->whereNumber('id');
        Route::post('/krs/validasi/{id}', [KrsSirkulasiController::class, 'validasi'])->whereNumber('id');
        Route::delete('/krs/validasi/{id}/mk/{mk}', [KrsSirkulasiController::class, 'hapusMataKuliah'])->whereNumber(['id', 'mk']);
    });

    // KRS mahasiswa: ringkasan tetap terbaca walau periode ditutup / tagihan belum lunas (alasan ditampilkan)
    Route::get('/krs/saya', [KrsSirkulasiController::class, 'saya'])->middleware('role:mahasiswa');

    // Rute Pengisian KRS Mahasiswa di HP (Dilindungi Role Mahasiswa, Keuangan & Periode Waktu)
    Route::middleware([
        'role:mahasiswa',
        \App\Http\Middleware\CekStatusPembayaranMiddleware::class,
        \App\Http\Middleware\CekPeriodeKrsMiddleware::class
    ])->group(function () {
        Route::get('/krs/form', [KrsController::class, 'getFormKrsApi']);
        Route::post('/krs/submit', [KrsController::class, 'submitKrsApi']);
    });

    // Dokumen PDF resmi mahasiswa (KRS final, KHS, transkrip, jadwal) — isi & kode verifikasi sama dengan web
    Route::get('/dokumen/{jenis}', [DokumenController::class, 'unduh'])->whereIn('jenis', ['krs', 'khs', 'transkrip', 'jadwal'])
        ->middleware(['role:mahasiswa', 'throttle:20,1']);

    // E-KTM digital (desain tetap, tidak mengikuti tema sentral)
    Route::get('/ktm', function (Request $request) {
        $mahasiswa = $request->user()->mahasiswa;
        abort_unless($mahasiswa, 404, 'Data mahasiswa tidak ditemukan.');
        return response()->json(\App\Support\Ktm::data($mahasiswa));
    });

    // Berkas privat (jawaban tugas, bukti bayar, dll.) dengan cek hak akses yang sama dengan web
    Route::get('/berkas/{jenis}/{id}/{sisi?}', [\App\Http\Controllers\BerkasController::class, 'unduh'])
        ->whereNumber('id')->whereIn('sisi', ['masuk', 'pulang'])->middleware('throttle:60,1');

    // Popup SIAKAD web di dalam aplikasi: tautan masuk otomatis sekali pakai
    Route::post('/web/sesi', [SesiWebController::class, 'buat'])->middleware('throttle:20,1');

    // 6b. VERUM (KELAS DARING) — aturan sama dengan web (VerumService + VerumKelasPolicy)
    Route::prefix('verum')->group(function () {
        Route::get('/kelas', [VerumApiController::class, 'index']);
        Route::get('/kelas-form', [VerumApiController::class, 'formKelas']);
        Route::post('/kelas', [VerumApiController::class, 'simpanKelas']);
        Route::get('/kelas/{kelas}', [VerumApiController::class, 'show']);
        Route::delete('/kelas/{kelas}', [VerumApiController::class, 'hapusKelas']);
        Route::patch('/kelas/{kelas}/vicon', [VerumApiController::class, 'vicon']);
        Route::post('/kelas/{kelas}/forum', [VerumApiController::class, 'kirimPostingan'])->middleware('throttle:30,1');
        Route::delete('/forum/{postingan}', [VerumApiController::class, 'hapusPostingan']);
        Route::post('/kelas/{kelas}/materi', [VerumApiController::class, 'simpanMateri']);
        Route::delete('/materi/{materi}', [VerumApiController::class, 'hapusMateri']);
        Route::post('/kelas/{kelas}/tugas', [VerumApiController::class, 'simpanTugas']);
        Route::get('/tugas/{tugas}', [VerumApiController::class, 'tugas']);
        Route::delete('/tugas/{tugas}', [VerumApiController::class, 'hapusTugas']);
        Route::post('/tugas/{tugas}/nilai', [VerumApiController::class, 'simpanNilai']);
        Route::post('/tugas/{tugas}/kumpulkan', [VerumApiController::class, 'kumpulkan'])->middleware('throttle:10,1');
        Route::post('/kelas/{kelas}/presensi', [VerumApiController::class, 'bukaPresensi']);
        Route::get('/presensi/{presensi}', [VerumApiController::class, 'presensi']);
        Route::patch('/presensi/{presensi}/jurnal', [VerumApiController::class, 'perbaruiJurnal']);
        Route::post('/presensi/{presensi}/hadir', [VerumApiController::class, 'hadir'])->middleware('throttle:10,1');
    });

    // 7. STATISTIK (Khusus Pimpinan & Admin)
    Route::get('/stats/mahasiswa-per-prodi', [DashboardController::class, 'mahasiswaPerProdi'])
        ->middleware('role:admin,rektorat,penjaminan_mutu,dosen,kaprodi');

    // 8. FITUR NOTIFIKASI
    Route::get('/notifikasi', function (Request $request) {
        return response()->json($request->user()->notifications()->latest()->get());
    });

    Route::post('/notifikasi/{id}/read', function (Request $request, $id) {
        $notif = $request->user()->notifications()->where('id', $id)->first();
        if ($notif) {
            $notif->markAsRead();
        }
        return response()->json(['status' => 'success']);
    });
});