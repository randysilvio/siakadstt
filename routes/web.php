<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ProgramStudiController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\KhsController;
use App\Http\Controllers\TranskripController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\DosenDashboardController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PerwalianController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\ValidasiKrsController;
use App\Http\Controllers\KaprodiDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // RUTE UNTUK MAHASISWA
    Route::middleware('mahasiswa')->group(function () {
        Route::middleware(['cek_pembayaran', 'cek_periode_krs'])->group(function () {
            Route::get('/krs', [KrsController::class, 'index'])->name('krs.index');
            Route::post('/krs', [KrsController::class, 'store'])->name('krs.store');
        });

        Route::get('/khs', [KhsController::class, 'index'])->name('khs.index');
        Route::get('/transkrip', [TranskripController::class, 'index'])->name('transkrip.index');
        Route::get('/khs/cetak', [CetakController::class, 'cetakKhs'])->name('khs.cetak');
        Route::get('/transkrip/cetak', [CetakController::class, 'cetakTranskrip'])->name('transkrip.cetak');
        Route::get('/krs/cetak', [CetakController::class, 'cetakKrs'])->name('krs.cetak.final');
        Route::get('/riwayat-pembayaran', [PembayaranController::class, 'riwayat'])->name('pembayaran.riwayat');
    });

    // RUTE UNTUK DOSEN
    Route::middleware('dosen')->group(function () {
         Route::get('/dosen/dashboard', [DosenDashboardController::class, 'index'])->name('dosen.dashboard');
         Route::get('/perwalian', [PerwalianController::class, 'index'])->name('perwalian.index');
         Route::post('/perwalian', [PerwalianController::class, 'store'])->name('perwalian.store');
         Route::delete('/perwalian/{mahasiswa}', [PerwalianController::class, 'destroy'])->name('perwalian.destroy');
    });

    // RUTE UNTUK KAPRODI
    Route::middleware('kaprodi')->group(function () {
        Route::get('/kaprodi/dashboard', [KaprodiDashboardController::class, 'index'])->name('kaprodi.dashboard');
        Route::get('/kaprodi/validasi-krs/{mahasiswa}', [ValidasiKrsController::class, 'show'])->name('kaprodi.krs.show');
        Route::patch('/kaprodi/validasi-krs/{mahasiswa}', [ValidasiKrsController::class, 'update'])->name('kaprodi.krs.update');
    });

    // RUTE BERSAMA UNTUK ADMIN & DOSEN
    Route::get('/nilai/{mataKuliah}', [NilaiController::class, 'show'])->name('nilai.show');
    Route::post('/nilai', [NilaiController::class, 'store'])->name('nilai.store');

    // GRUP RUTE UNTUK KEUANGAN (ADMIN & DOSEN KEUANGAN)
    Route::middleware('keuangan')->group(function() {
        Route::resource('pembayaran', PembayaranController::class)->except(['show', 'edit', 'update']);
        Route::patch('/pembayaran/{pembayaran}/lunas', [PembayaranController::class, 'tandaiLunas'])->name('pembayaran.lunas');
    });

    // GRUP RUTE KHUSUS HANYA UNTUK ADMIN
    Route::middleware('admin')->group(function () {
        Route::resource('mahasiswa', MahasiswaController::class);
        Route::resource('program-studi', ProgramStudiController::class);
        Route::resource('mata-kuliah', MataKuliahController::class);
        Route::resource('dosen', DosenController::class);
        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
        Route::resource('pengumuman', PengumumanController::class);
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::post('/pengaturan', [PengaturanController::class, 'store'])->name('pengaturan.store');
        Route::resource('tahun-akademik', TahunAkademikController::class);
        Route::patch('/tahun-akademik/{tahunAkademik}/set-active', [TahunAkademikController::class, 'setActive'])->name('tahun-akademik.set-active');
    });
    
});

require __DIR__.'/auth.php';
Route::get('/storage-link', function () {
    $targetFolder = storage_path('app/public');
    $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';
    symlink($targetFolder, $linkFolder);
    return 'Symlink created successfully';
});