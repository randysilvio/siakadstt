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
// Controller untuk manajemen Admin
use App\Http\Controllers\Admin\SlideshowController;
use App\Http\Controllers\Admin\DokumenPublikController;
use App\Http\Controllers\Admin\EvaluasiSesiController;
use App\Http\Controllers\Admin\EvaluasiPertanyaanController;
use App\Http\Controllers\Admin\EvaluasiHasilController;
use App\Http\Controllers\Admin\KurikulumController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AbsensiController;
// Controller untuk halaman publik
use App\Http\Controllers\Public\DosenProfileController;
// Controller untuk Perpustakaan
use App\Http\Controllers\Perpustakaan\PeminjamanController;
// Middleware
use App\Http\Middleware\CekStatusPembayaranMiddleware;
use App\Http\Middleware\CekPeriodeKrsMiddleware;
use App\Http\Middleware\KaprodiMiddleware;
use App\Http\Controllers\ChatbotController;
// Controller untuk peran institusional
use App\Http\Controllers\PenjaminanMutuController;
use App\Http\Controllers\RektoratController;
// Controller untuk Mahasiswa
use App\Http\Controllers\EvaluasiController;
// Controller untuk Dosen
use App\Http\Controllers\DosenDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- RUTE PUBLIK ---
Route::get('/', [PublicController::class, 'index'])->name('welcome');
Route::get('/berita', [PublicController::class, 'semuaBerita'])->name('berita.index');
Route::get('/direktori-dosen', [DosenProfileController::class, 'index'])->name('dosen.public.index');
Route::get('/dosen/{dosen:nidn}', [DosenProfileController::class, 'show'])->name('dosen.public.show');
Route::get('/pengumuman/{pengumuman}', [PublicController::class, 'showPengumuman'])->name('pengumuman.public.show');


// --- RUTE OTENTIKASI & DASHBOARD ---
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// --- GRUP RUTE YANG MEMBUTUHKAN LOGIN ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.update.photo');
    Route::post('/chatbot-handle', [ChatbotController::class, 'handle'])->name('chatbot.handle');

    // Rute umum
    Route::get('/kalender-akademik', [KalenderController::class, 'halamanKalender'])->name('kalender.halaman');
    Route::get('/kalender-akademik/events', [KalenderController::class, 'getEvents'])->name('kalender.events');

    // Rute untuk Modul Verum
    Route::prefix('verum')->name('verum.')->group(function() {
        Route::get('/', [VerumController::class, 'index'])->name('index');
        Route::get('/kelas/create', [VerumController::class, 'create'])->name('create')->middleware('role:dosen');
        Route::post('/kelas', [VerumController::class, 'store'])->name('store')->middleware('role:dosen');
        Route::get('/kelas/{verum_kela}', [VerumController::class, 'show'])->name('show');
        
        // Rute untuk Meeting Online
        Route::patch('/kelas/{verum_kela}/start-meeting', [VerumController::class, 'startMeeting'])->name('meeting.start')->middleware('role:dosen');
        Route::patch('/kelas/{verum_kela}/stop-meeting', [VerumController::class, 'stopMeeting'])->name('meeting.stop')->middleware('role:dosen');

        Route::post('/kelas/{verum_kela}/forum', [VerumController::class, 'storePost'])->name('forum.store');
        Route::post('/kelas/{verum_kela}/materi', [VerumMateriController::class, 'store'])->name('materi.store')->middleware('role:dosen');
        Route::delete('/materi/{verum_materi}', [VerumMateriController::class, 'destroy'])->name('materi.destroy')->middleware('role:dosen');
        Route::post('/kelas/{verum_kela}/tugas', [VerumTugasController::class, 'store'])->name('tugas.store')->middleware('role:dosen');
        Route::post('/tugas/{verum_tuga}/kumpulkan', [VerumTugasController::class, 'storePengumpulan'])->name('tugas.kumpulkan')->middleware('role:mahasiswa');
        Route::post('/kelas/{verum_kela}/presensi', [VerumPresensiController::class, 'store'])->name('presensi.store')->middleware('role:dosen');
        Route::post('/presensi/{verum_presensi}/hadir', [VerumPresensiController::class, 'storeKehadiran'])->name('presensi.hadir')->middleware('role:mahasiswa');
    });

    // BLOK UNTUK MODUL PERPUSTAKAAN
    Route::prefix('perpustakaan')->name('perpustakaan.')->group(function() {
        Route::middleware('role:pustakawan')->group(function() {
            Route::get('/', [PerpustakaanController::class, 'dashboard'])->name('index');
            Route::resource('koleksi', KoleksiController::class);
            Route::resource('peminjaman', PeminjamanController::class)->only(['index', 'create', 'store']);
            Route::get('/pengembalian', [PeminjamanController::class, 'showReturnForm'])->name('peminjaman.returnForm');
            Route::post('/pengembalian', [PeminjamanController::class, 'processReturn'])->name('peminjaman.processReturn');
            Route::get('/peminjaman/history', [PeminjamanController::class, 'history'])->name('peminjaman.history');
        });
    });

    // RUTE UNTUK MAHASISWA
    Route::middleware('role:mahasiswa')->group(function () {
        Route::middleware([CekStatusPembayaranMiddleware::class, CekPeriodeKrsMiddleware::class])->group(function () {
            Route::get('/krs', [KrsController::class, 'index'])->name('krs.index');
            Route::post('/krs', [KrsController::class, 'store'])->name('krs.store');
            
            // Rute Hapus Item KRS
            Route::delete('/krs/{krs}', [KrsController::class, 'destroy'])->name('krs.destroy');
        });
        Route::get('/khs', [KhsController::class, 'index'])->name('khs.index');
        Route::get('/transkrip', [TranskripController::class, 'index'])->name('transkrip.index');
        Route::get('/khs/cetak', [CetakController::class, 'cetakKhs'])->name('khs.cetak');
        Route::get('/transkrip/cetak', [CetakController::class, 'cetakTranskrip'])->name('transkrip.cetak');
        Route::get('/krs/cetak', [CetakController::class, 'cetakKrs'])->name('krs.cetak.final');
        
        // [BARU] Rute Cetak Jadwal Kuliah
        Route::get('/jadwal/cetak', [CetakController::class, 'cetakJadwal'])->name('jadwal.cetak');
        
        Route::get('/riwayat-pembayaran', [PembayaranController::class, 'riwayat'])->name('pembayaran.riwayat');

        // Rute untuk Evaluasi Dosen
        Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
        Route::get('/evaluasi/{mataKuliah}', [EvaluasiController::class, 'show'])->name('evaluasi.show');
        Route::post('/evaluasi/{mataKuliah}', [EvaluasiController::class, 'store'])->name('evaluasi.store');
    });

    // RUTE UNTUK DOSEN
    Route::middleware('role:dosen')->group(function () {
         Route::get('/dosen/dashboard', [DosenDashboardController::class, 'index'])->name('dosen.dashboard');
         
         // Rute Upload RPS Dosen
         Route::post('/dosen/mata-kuliah/{mataKuliah}/upload-rps', [DosenDashboardController::class, 'uploadRps'])->name('dosen.upload_rps');
         
         // Manajemen Perwalian
         Route::get('/perwalian', [PerwalianController::class, 'index'])->name('perwalian.index');
         Route::post('/perwalian', [PerwalianController::class, 'store'])->name('perwalian.store');
         Route::delete('/perwalian/{mahasiswa}', [PerwalianController::class, 'destroy'])->name('perwalian.destroy');

         // Detail Perwalian & Validasi KRS
         Route::get('/perwalian/{mahasiswa}', [PerwalianController::class, 'show'])->name('perwalian.show');
         Route::patch('/perwalian/{mahasiswa}/status', [PerwalianController::class, 'updateStatus'])->name('perwalian.updateStatus');

         // Rute Dosen menghapus Mata Kuliah Mahasiswa Bimbingan
         Route::delete('/perwalian/krs/{mahasiswa}/{mk}', [PerwalianController::class, 'destroyKrs'])->name('perwalian.krs.destroy');
    });

    // RUTE UNTUK KAPRODI
    Route::middleware(KaprodiMiddleware::class)->group(function () {
        Route::get('/kaprodi/dashboard', [KaprodiDashboardController::class, 'index'])->name('kaprodi.dashboard');
        Route::get('/kaprodi/validasi-krs/{mahasiswa}', [ValidasiKrsController::class, 'show'])->name('kaprodi.krs.show');
        Route::patch('/kaprodi/validasi-krs/{mahasiswa}', [ValidasiKrsController::class, 'update'])->name('kaprodi.krs.update');
    });

    // RUTE UNTUK PENJAMINAN MUTU
    Route::middleware('role:penjaminan_mutu')->prefix('penjaminan-mutu')->name('mutu.')->group(function () {
        Route::get('/dashboard', [PenjaminanMutuController::class, 'dashboard'])->name('dashboard');
    });

    // RUTE UNTUK REKTORAT
    Route::middleware('role:rektorat')->prefix('rektorat')->name('rektorat.')->group(function () {
        Route::get('/dashboard', [RektoratController::class, 'dashboard'])->name('dashboard');
    });


    // RUTE BERSAMA UNTUK ADMIN & DOSEN & KAPRODI
    Route::get('/nilai/{mataKuliah}', [NilaiController::class, 'show'])->name('nilai.show');
    Route::post('/nilai', [NilaiController::class, 'store'])->name('nilai.store');


    // =================================================================
    // GRUP RUTE UNTUK KEUANGAN (ADMIN DILARANG MASUK SINI)
    // =================================================================
    // Perubahan: Hanya role 'keuangan'
    Route::middleware(['role:keuangan'])->group(function() {
        Route::get('/pembayaran/generate', [PembayaranController::class, 'generate'])->name('pembayaran.generate');
        Route::post('/pembayaran/generate', [PembayaranController::class, 'storeGenerate'])->name('pembayaran.storeGenerate');
        Route::get('/pembayaran/cetak', [PembayaranController::class, 'cetakLaporan'])->name('pembayaran.cetak');
        
        // Resource CRUD lengkap (Kecuali 'show')
        // Ini otomatis mengaktifkan rute edit/update untuk cicilan
        Route::resource('pembayaran', PembayaranController::class)->except(['show']);
        
        Route::patch('/pembayaran/{pembayaran}/lunas', [PembayaranController::class, 'tandaiLunas'])->name('pembayaran.lunas');
    });


    // GRUP RUTE KHUSUS HANYA UNTUK ADMIN
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard/chart/mahasiswa-per-prodi', [DashboardController::class, 'mahasiswaPerProdi'])->name('dashboard.chart.mahasiswa-per-prodi');

        // Rute Export/Import
        Route::get('/mahasiswa/import/template', [MahasiswaController::class, 'downloadImportTemplate'])->name('mahasiswa.import.template');
        Route::get('/mahasiswa/export', [MahasiswaController::class, 'export'])->name('mahasiswa.export');
        Route::post('/mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
        Route::get('/dosen/export', [DosenController::class, 'export'])->name('dosen.export');
        Route::post('/dosen/import', [DosenController::class, 'import'])->name('dosen.import');
        Route::get('/dosen/import/template', [DosenController::class, 'downloadTemplate'])->name('dosen.import.template');
        Route::get('/mata-kuliah/export', [MataKuliahController::class, 'export'])->name('mata-kuliah.export');
        Route::post('/mata-kuliah/import', [MataKuliahController::class, 'import'])->name('mata-kuliah.import');
        Route::get('/mata-kuliah/import/template', [MataKuliahController::class, 'downloadTemplate'])->name('mata-kuliah.import.template');
        
        Route::get('/tendik/create', [TendikController::class, 'create'])->name('tendik.create');
        Route::post('/tendik', [TendikController::class, 'store'])->name('tendik.store');

        // Rute Manajemen Absensi
        Route::prefix('absensi')->name('absensi.')->group(function () {
            Route::get('/laporan', [AbsensiController::class, 'laporanIndex'])->name('laporan.index');
            
            // [BARU] Rute untuk Cetak Laporan
            Route::get('/laporan/cetak', [AbsensiController::class, 'laporanCetak'])->name('laporan.cetak');

            Route::get('/lokasi', [AbsensiController::class, 'lokasiIndex'])->name('lokasi.index');
            Route::get('/lokasi/create', [AbsensiController::class, 'lokasiCreate'])->name('lokasi.create');
            Route::post('/lokasi', [AbsensiController::class, 'lokasiStore'])->name('lokasi.store');
            Route::get('/lokasi/{lokasi}/edit', [AbsensiController::class, 'lokasiEdit'])->name('lokasi.edit');
            Route::put('/lokasi/{lokasi}', [AbsensiController::class, 'lokasiUpdate'])->name('lokasi.update');
            Route::delete('/lokasi/{lokasi}', [AbsensiController::class, 'lokasiDestroy'])->name('lokasi.destroy');
        });

        Route::resource('mahasiswa', MahasiswaController::class);
        Route::resource('program-studi', ProgramStudiController::class);
        Route::resource('kurikulum', KurikulumController::class)->except(['show']);
        Route::patch('/kurikulum/{kurikulum}/set-active', [KurikulumController::class, 'setActive'])->name('kurikulum.setActive');
        Route::resource('mata-kuliah', MataKuliahController::class);
        Route::resource('dosen', DosenController::class);
        
        Route::resource('pengumuman', PengumumanController::class);
        Route::resource('tahun-akademik', TahunAkademikController::class);
        Route::resource('kalender', KalenderController::class)->except(['show']);
        Route::resource('slideshows', SlideshowController::class);
        Route::resource('dokumen-publik', DokumenPublikController::class);
        
        Route::resource('evaluasi-sesi', EvaluasiSesiController::class)->except(['show']);
        Route::resource('evaluasi-pertanyaan', EvaluasiPertanyaanController::class)->except(['show']);

        // Rute Hasil Evaluasi (Admin)
        Route::get('/evaluasi-hasil', [EvaluasiHasilController::class, 'index'])->name('evaluasi-hasil.index');
        Route::get('/evaluasi-hasil/{sesi}/{dosen}', [EvaluasiHasilController::class, 'show'])->name('evaluasi-hasil.show');
        Route::get('/evaluasi-hasil/{sesi}/{dosen}/cetak', [EvaluasiHasilController::class, 'cetak'])->name('evaluasi-hasil.cetak');
        
        Route::resource('user', UserController::class)->except(['create', 'store', 'show']);

        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::post('/pengaturan', [PengaturanController::class, 'store'])->name('pengaturan.store');
        Route::patch('/tahun-akademik/{tahunAkademik}/set-active', [TahunAkademikController::class, 'setActive'])->name('tahun-akademik.set-active');
    });
});

require __DIR__.'/auth.php';