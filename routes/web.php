<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ProgramStudiController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\KhsController;
use App\Http\Controllers\TranskripController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PerwalianController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\ValidasiKrsController;
use App\Http\Controllers\KaprodiDashboardController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\VerumController;
use App\Http\Controllers\VerumMateriController;
use App\Http\Controllers\VerumTugasController;
use App\Http\Controllers\VerumPresensiController;
use App\Http\Controllers\PerpustakaanController;
use App\Http\Controllers\KoleksiController;
use App\Http\Controllers\TendikController;
// Controller baru untuk manajemen Admin
use App\Http\Controllers\Admin\SlideshowController;
use App\Http\Controllers\Admin\DokumenPublikController;
// Middleware
use App\Http\Middleware\CekStatusPembayaranMiddleware;
use App\Http\Middleware\CekPeriodeKrsMiddleware;
use App\Http\Controllers\ChatbotController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- RUTE PUBLIK ---
Route::get('/', [PublicController::class, 'index'])->name('welcome');
Route::get('/berita', [PublicController::class, 'semuaBerita'])->name('berita.index');


// --- RUTE OTENTIKASI & DASHBOARD ---
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// --- GRUP RUTE YANG MEMBUTUHKAN LOGIN ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/chatbot-handle', [ChatbotController::class, 'handle'])->name('chatbot.handle');

    // Rute umum
    Route::get('/kalender-akademik', [KalenderController::class, 'halamanKalender'])->name('kalender.halaman');
    Route::get('/kalender-akademik/events', [KalenderController::class, 'getEvents'])->name('kalender.events');
    
    // Rute untuk Modul Verum
    Route::prefix('verum')->name('verum.')->group(function() {
        Route::get('/', [VerumController::class, 'index'])->name('index');
        Route::get('/kelas/create', [VerumController::class, 'create'])->name('create')->middleware('dosen');
        Route::post('/kelas', [VerumController::class, 'store'])->name('store')->middleware('dosen');
        Route::get('/kelas/{verum_kela}', [VerumController::class, 'show'])->name('show');
        Route::post('/kelas/{verum_kela}/forum', [VerumController::class, 'storePost'])->name('forum.store');
        Route::post('/kelas/{verum_kela}/materi', [VerumMateriController::class, 'store'])->name('materi.store')->middleware('dosen');
        Route::delete('/materi/{verum_materi}', [VerumMateriController::class, 'destroy'])->name('materi.destroy')->middleware('dosen');
        Route::post('/kelas/{verum_kela}/tugas', [VerumTugasController::class, 'store'])->name('tugas.store')->middleware('dosen');
        Route::post('/tugas/{verum_tuga}/kumpulkan', [VerumTugasController::class, 'storePengumpulan'])->name('tugas.kumpulkan')->middleware('mahasiswa');
        Route::post('/kelas/{verum_kela}/presensi', [VerumPresensiController::class, 'store'])->name('presensi.store')->middleware('dosen');
        Route::post('/presensi/{verum_presensi}/hadir', [VerumPresensiController::class, 'storeKehadiran'])->name('presensi.hadir')->middleware('mahasiswa');
    });

    // BLOK UNTUK MODUL PERPUSTAKAAN
    Route::prefix('perpustakaan')->name('perpustakaan.')->group(function() {
        Route::get('/', [PerpustakaanController::class, 'index'])->name('index');
        Route::middleware('pustakawan')->group(function() {
            Route::resource('koleksi', KoleksiController::class);
        });
    });

    // RUTE UNTUK MAHASISWA
    Route::middleware('mahasiswa')->group(function () {
        Route::middleware([CekStatusPembayaranMiddleware::class, CekPeriodeKrsMiddleware::class])->group(function () {
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
    Route::middleware('admin_or_dosen')->group(function() {
        Route::get('/nilai/{mataKuliah}', [NilaiController::class, 'show'])->name('nilai.show');
        Route::post('/nilai', [NilaiController::class, 'store'])->name('nilai.store');
    });

    // GRUP RUTE UNTUK KEUANGAN
    Route::middleware('keuangan_tendik')->group(function() {
        Route::resource('pembayaran', PembayaranController::class)->except(['show', 'edit', 'update']);
        Route::patch('/pembayaran/{pembayaran}/lunas', [PembayaranController::class, 'tandaiLunas'])->name('pembayaran.lunas');
    });

    // GRUP RUTE KHUSUS HANYA UNTUK ADMIN
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard/chart/mahasiswa-per-prodi', [DashboardController::class, 'mahasiswaPerProdi'])->name('dashboard.chart.mahasiswa-per-prodi');
        
        // Rute Export/Import untuk Mahasiswa
        Route::get('/mahasiswa/import/template', [MahasiswaController::class, 'downloadImportTemplate'])->name('mahasiswa.import.template');
        Route::get('/mahasiswa/export', [MahasiswaController::class, 'export'])->name('mahasiswa.export');
        Route::post('/mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
        
        // Rute Export/Import untuk Dosen
        Route::get('/dosen/export', [DosenController::class, 'export'])->name('dosen.export');
        Route::post('/dosen/import', [DosenController::class, 'import'])->name('dosen.import');
        Route::get('/dosen/import/template', [DosenController::class, 'downloadTemplate'])->name('dosen.import.template');

        // Rute Export/Import untuk Mata Kuliah
        Route::get('/mata-kuliah/export', [MataKuliahController::class, 'export'])->name('mata-kuliah.export');
        Route::post('/mata-kuliah/import', [MataKuliahController::class, 'import'])->name('mata-kuliah.import');
        Route::get('/mata-kuliah/import/template', [MataKuliahController::class, 'downloadTemplate'])->name('mata-kuliah.import.template');

        // Rute Resource
        Route::resource('mahasiswa', MahasiswaController::class);
        Route::resource('program-studi', ProgramStudiController::class);
        Route::resource('mata-kuliah', MataKuliahController::class);
        Route::resource('dosen', DosenController::class);
        Route::resource('pengumuman', PengumumanController::class);
        Route::resource('tahun-akademik', TahunAkademikController::class);
        Route::resource('kalender', KalenderController::class)->except(['show']);
        Route::resource('slideshows', SlideshowController::class);
        Route::resource('dokumen-publik', DokumenPublikController::class);

        // Rute-rute tunggal yang sudah ada
        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::post('/pengaturan', [PengaturanController::class, 'store'])->name('pengaturan.store');
        Route::patch('/tahun-akademik/{tahunAkademik}/set-active', [TahunAkademikController::class, 'setActive'])->name('tahun-akademik.set-active');
        Route::get('/tendik/create', [TendikController::class, 'create'])->name('tendik.create');
        Route::post('/tendik', [TendikController::class, 'store'])->name('tendik.store');
    });
});

require __DIR__.'/auth.php';