<?php

use App\Models\AbsensiPegawai;
use App\Models\Dosen;
use App\Models\DosenTridharma;
use App\Models\EvaluasiJawaban;
use App\Models\EvaluasiPertanyaan;
use App\Models\EvaluasiSesi;
use App\Models\KegiatanAkademik;
use App\Models\Koleksi;
use App\Models\Kurikulum;
use App\Models\LokasiKerja;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\PengajuanSurat;
use App\Models\PengaturanAbsensi;
use App\Models\PerpustakaanEksemplar;
use App\Models\PmbPeriod;
use App\Models\SuratKeputusan;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

/** CSV dengan baris banner + baris judul (sama seperti template Excel) untuk diunggah ke fitur impor. */
function csvImpor(array $judul, array $baris): UploadedFile
{
    $isi = "TEMPLATE\n" . implode(',', $judul) . "\n";
    foreach ($baris as $b) {
        $isi .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $b)) . "\n";
    }
    return UploadedFile::fake()->createWithContent('impor.csv', $isi);
}

// ---------------------------------------------------------------- Kalender

test('kalender: dibuka tanpa parameter tidak error, dan agenda untuk Kaprodi sampai ke dosen Kaprodi', function () {
    $d = $this->seedAkademik();
    $kUser = $this->buatUser('Kaprodi', 'kap@x.test', ['dosen']);
    $kap = Dosen::create(['user_id' => $kUser->id, 'nidn' => '777', 'nama_lengkap' => 'Kaprodi', 'jenis_pengajar' => 'Dosen']);
    $d['prodi']->update(['kaprodi_dosen_id' => $kap->id]);
    $roleKaprodi = $this->role('kaprodi');

    $this->actingAs($d['admin'])->post(route('admin.kalender.store'), [
        'judul_kegiatan' => 'Rapat Kaprodi', 'tanggal_mulai' => now()->toDateString(), 'tanggal_selesai' => now()->toDateString(),
        'target_roles' => [$roleKaprodi->id],
    ])->assertRedirect(route('admin.kalender.index'));

    expect($kUser->notifications()->count())->toBe(1)->and($d['dUser']->notifications()->count())->toBe(0);
    $this->actingAs($kUser)->getJson(route('kalender.events'))->assertOk()->assertJsonFragment(['title' => 'Rapat Kaprodi']);
    $this->actingAs($d['dUser'])->getJson(route('kalender.events'))->assertOk()->assertJsonMissing(['title' => 'Rapat Kaprodi']);
});

test('beranda publik tidak menampilkan agenda internal', function () {
    $this->seedAkademik();
    $internal = KegiatanAkademik::create(['judul_kegiatan' => 'Rapat Senat Tertutup', 'tanggal_mulai' => now()->addDay(), 'tanggal_selesai' => now()->addDay()]);
    $internal->roles()->sync([$this->role('dosen')->id]);
    $umum = KegiatanAkademik::create(['judul_kegiatan' => 'Ujian Tengah Semester', 'tanggal_mulai' => now()->addDays(2), 'tanggal_selesai' => now()->addDays(3)]);
    $umum->roles()->sync([$this->role('mahasiswa')->id]);

    $this->get('/')->assertOk()->assertSee('Ujian Tengah Semester')->assertDontSee('Rapat Senat Tertutup');
});

// ---------------------------------------------------------------- Keuangan & Rektorat

test('periode tagihan dipilih dari tahun akademik dan laporan rektorat mengenali label lama', function () {
    $d = $this->seedAkademik();
    $keu = $this->buatUser('Keu', 'keu@x.test', ['keuangan']);
    $rektor = $this->buatUser('Rektor', 'rek@x.test', ['rektorat']);

    $this->actingAs($keu)->get(route('pembayaran.create'))->assertOk()->assertSee('2026/2027 Ganjil (AKTIF)');
    $this->actingAs($keu)->get(route('pembayaran.generate'))->assertOk()->assertSee('2026/2027 Ganjil');

    $this->actingAs($keu)->post(route('pembayaran.storeGenerate'), ['semester' => '2026/2027 Ganjil', 'jumlah' => 1000000, 'jenis_pembayaran' => 'spp'])
        ->assertSessionHas('success');
    // label sama beda huruf tidak menggandakan tagihan
    $this->actingAs($keu)->post(route('pembayaran.storeGenerate'), ['semester' => '2026/2027 GANJIL', 'jumlah' => 1000000, 'jenis_pembayaran' => 'spp']);
    expect(Pembayaran::where('jenis_pembayaran', 'spp')->count())->toBe(1);

    // tagihan lama berlabel bebas tetap terhitung di dashboard Rektorat
    Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 500000, 'semester' => 'GANJIL 2026/2027',
        'status' => 'lunas', 'jenis_pembayaran' => 'uang_gedung', 'tanggal_bayar' => now()]);
    $this->actingAs($rektor)->get(route('rektorat.dashboard'))->assertOk()->assertViewHas('totalTagihanSemesterIni', 1500000)
        ->assertViewHas('pendapatanSemesterIni', 500000);
    $this->actingAs($rektor)->get(route('rektorat.cetak'))->assertOk();
});

test('pelunasan lewat form edit mencatat petugas verifikator', function () {
    $d = $this->seedAkademik();
    $keu = $this->buatUser('Keu', 'keu@x.test', ['keuangan']);
    $p = Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1000, 'semester' => 'X', 'status' => 'belum_lunas', 'jenis_pembayaran' => 'spp']);

    $this->actingAs($keu)->get(route('pembayaran.edit', $p))->assertOk()->assertSee('X (DATA LAMA)');
    $this->actingAs($keu)->put(route('pembayaran.update', $p), ['jumlah' => 1000, 'semester' => '2026/2027 Ganjil', 'status' => 'lunas'])->assertSessionHas('success');

    $p->refresh();
    expect($p->status)->toBe('lunas')->and((int) $p->diverifikasi_oleh)->toBe($keu->id)->and($p->semester)->toBe('2026/2027 Ganjil');
});

// ---------------------------------------------------------------- Administrasi

test('nomor SK wajib unik', function () {
    $this->seedAkademik();
    $bau = $this->buatUser('BAU', 'bau@x.test', ['administrasi_umum']);
    SuratKeputusan::create(['jenis_surat' => 'Surat Keputusan (SK)', 'nomor_surat' => '01/SK/2026', 'judul' => 'SK A', 'status' => 'Draf']);

    $this->actingAs($bau)->post(route('administrasi.surat-keputusan.store'), ['jenis_surat' => 'Surat Keputusan (SK)', 'nomor_surat' => '01/SK/2026', 'judul' => 'SK B'])
        ->assertSessionHasErrors('nomor_surat');
    expect(SuratKeputusan::count())->toBe(1);
});

test('tanggal surat mahasiswa tetap tanggal persetujuan pertama', function () {
    $d = $this->seedAkademik();
    $bau = $this->buatUser('BAU', 'bau@x.test', ['administrasi_umum']);
    $s = PengajuanSurat::create(['mahasiswa_id' => $d['mhs']->id, 'jenis_surat' => 'Surat Aktif', 'keperluan' => 'Beasiswa', 'status' => 'Menunggu Verifikasi', 'kode_verifikasi' => 'SRT-X1']);

    $this->travelTo(now()->subDays(5));
    $this->actingAs($bau)->patch(route('admin.surat-mandiri.updateStatus', $s), ['status' => 'Disetujui']);
    $tanggal = $s->fresh()->tanggal_disetujui->toDateString();
    $this->travelBack();

    $this->actingAs($bau)->patch(route('admin.surat-mandiri.updateStatus', $s), ['status' => 'Disetujui', 'catatan_admin' => 'koreksi catatan']);
    expect($s->fresh()->tanggal_disetujui->toDateString())->toBe($tanggal);
});

// ---------------------------------------------------------------- Riwayat tidak boleh ikut terhapus

test('sesi/pertanyaan EDOM yang sudah dijawab dan lokasi kerja yang punya absensi tidak dapat dihapus', function () {
    $d = $this->seedAkademik();
    $sesi = EvaluasiSesi::create(['nama_sesi' => 'EDOM', 'tahun_akademik_id' => $d['ta']->id, 'tanggal_mulai' => now(), 'tanggal_selesai' => now()->addDay(), 'is_active' => true]);
    $q = EvaluasiPertanyaan::create(['pertanyaan' => 'Tepat waktu', 'tipe_jawaban' => 'skala_1_5', 'is_active' => true, 'urutan' => 1]);
    EvaluasiJawaban::create(['evaluasi_sesi_id' => $sesi->id, 'mahasiswa_id' => $d['mhs']->id, 'dosen_id' => $d['dosen']->id, 'mata_kuliah_id' => $d['mk']->id,
        'evaluasi_pertanyaan_id' => $q->id, 'jawaban_skala' => 4]);
    $lokasi = LokasiKerja::create(['nama_lokasi' => 'Kampus', 'latitude' => -2.9, 'longitude' => 132.3, 'radius_toleransi_meter' => 100]);
    AbsensiPegawai::create(['user_id' => $d['dUser']->id, 'lokasi_kerja_id' => $lokasi->id, 'tanggal_absensi' => now()->toDateString(), 'status_kehadiran' => 'Hadir']);

    $this->actingAs($d['admin'])->delete(route('admin.evaluasi-sesi.destroy', $sesi))->assertSessionHas('error');
    $this->actingAs($d['admin'])->delete(route('admin.evaluasi-pertanyaan.destroy', $q))->assertSessionHas('error');
    $this->actingAs($d['admin'])->delete(route('admin.absensi.lokasi.destroy', $lokasi))->assertSessionHas('error');

    expect(EvaluasiJawaban::count())->toBe(1)->and(AbsensiPegawai::count())->toBe(1);
});

test('check-in absensi tetap jalan bila jam masuk tersimpan tanpa detik', function () {
    $d = $this->seedAkademik();
    Storage::fake('local');
    PengaturanAbsensi::create(['jam_masuk' => '08:00', 'jam_pulang' => '16:00', 'toleransi_terlambat_menit' => 15]);
    LokasiKerja::create(['nama_lokasi' => 'Kampus', 'latitude' => -2.9, 'longitude' => 132.3, 'radius_toleransi_meter' => 100]);

    $this->actingAs($d['dUser'], 'sanctum')->postJson('/api/absensi/check-in', [
        'latitude' => -2.9, 'longitude' => 132.3, 'foto_check_in' => UploadedFile::fake()->image('selfie.jpg'),
    ])->assertCreated();

    $this->actingAs($d['admin'])->get(route('admin.absensi.laporan.cetak'))->assertOk();
});

// ---------------------------------------------------------------- BKD

test('BKD hanya menghitung mata kuliah & tridharma semester aktif', function () {
    $d = $this->seedAkademik(); // TA aktif Ganjil, MK semester 1 (ganjil) 3 SKS × 2 prodi
    MataKuliah::create(['kurikulum_id' => $d['mk']->kurikulum_id, 'kode_mk' => 'TH202', 'nama_mk' => 'Etika', 'sks' => 3, 'semester' => 2, 'dosen_id' => $d['dosen']->id]);
    DosenTridharma::create(['dosen_id' => $d['dosen']->id, 'unsur' => 'penelitian', 'kategori' => 'Jurnal', 'judul' => 'A', 'tahun' => 2026, 'semester' => 'Ganjil', 'sks_bkd' => 2]);
    DosenTridharma::create(['dosen_id' => $d['dosen']->id, 'unsur' => 'penelitian', 'kategori' => 'Jurnal', 'judul' => 'B', 'tahun' => 2026, 'semester' => 'Genap', 'sks_bkd' => 2]);

    $bkd = $d['dosen']->fresh()->hitungRingkasanBkd();

    expect($bkd['sks_penelitian'])->toBe(2.0)
        // Dogmatika 3 SKS × 2 kelas paralel (A,B) + Didaktik 3 SKS × 1 kelas + 1 mahasiswa wali × 0,1
        ->and($bkd['sks_pengajaran'])->toBe(9.0)->and($bkd['jumlah_kelas_ajar'])->toBe(3)
        ->and($bkd['sks_pendidikan'])->toBe(9.1);
});

// ---------------------------------------------------------------- Portal publik & pencarian

test('halaman publik prodi tidak menampilkan mata kuliah prodi lain', function () {
    $d = $this->seedAkademik();
    $prodiBaru = \App\Models\ProgramStudi::create(['nama_prodi' => 'Musik Gereja', 'kode_prodi' => 'MG']);

    $this->get(route('publik.prodi.show', $prodiBaru))->assertOk()->assertDontSee('Dogmatika')->assertDontSee('Didaktik');
    $this->get(route('publik.prodi.show', $d['prodi']))->assertOk()->assertSee('Dogmatika')->assertDontSee('Didaktik');
});

test('akun berperan dosen tanpa data dosen tidak dapat mencari seluruh mahasiswa', function () {
    $this->seedAkademik();
    $hantu = $this->buatUser('Tanpa Profil', 'hantu@x.test', ['dosen']);

    $hasil = $this->actingAs($hantu)->getJson(route('global.search', ['q' => 'Mhs']))->assertOk()->json('results');
    expect(collect($hasil)->where('category', 'MAHASISWA'))->toBeEmpty();
});

// ---------------------------------------------------------------- Perpustakaan

test('pengembalian buku yang dikirim dua kali tidak menambah stok dua kali', function () {
    $d = $this->seedAkademik();
    $pus = $this->buatUser('Pustakawan', 'pus@x.test', ['pustakawan']);
    $buku = Koleksi::create(['judul' => 'Buku', 'pengarang' => 'A', 'penerbit' => 'B', 'tahun_terbit' => 2020, 'jumlah_stok' => 2, 'jumlah_tersedia' => 0, 'lokasi_rak' => 'A1']);
    $e1 = PerpustakaanEksemplar::create(['koleksi_id' => $buku->id, 'kode_barcode' => 'BK-1', 'status_fisik' => 'Dipinjam']);
    PerpustakaanEksemplar::create(['koleksi_id' => $buku->id, 'kode_barcode' => 'BK-2', 'status_fisik' => 'Dipinjam']);
    $pinjam = Peminjaman::create(['koleksi_id' => $buku->id, 'eksemplar_id' => $e1->id, 'user_id' => $d['mUser']->id, 'tanggal_pinjam' => now(), 'jatuh_tempo' => now()->addWeek(), 'status' => 'Dipinjam']);

    $this->actingAs($pus)->post(route('perpustakaan.peminjaman.processReturn'), ['peminjaman_id' => $pinjam->id])->assertSessionHas('success');
    $this->actingAs($pus)->post(route('perpustakaan.peminjaman.processReturn'), ['peminjaman_id' => $pinjam->id])->assertSessionHas('error');

    expect($buku->fresh()->jumlah_tersedia)->toBe(1);
});

// ---------------------------------------------------------------- Impor Excel

test('impor mahasiswa menyimpan semua kolom template dan impor ulang tidak menghapus data', function () {
    $d = $this->seedAkademik();
    $judul = ['NIM', 'Nama Lengkap', 'Program Studi', 'Angkatan', 'Dosen Wali', 'NIK', 'Kelurahan', 'Kode Pos', 'Nama Ayah', 'Pekerjaan Ibu'];

    $this->actingAs($d['admin'])->post(route('admin.mahasiswa.import'), ['file' => csvImpor($judul, [
        ['2026TH050', 'Paulus Baru', 'TH', '2026', '111', '9201010101060050', 'Wagom', '98611', 'Yakob', 'Guru'],
    ])])->assertSessionHas('success');

    $m = Mahasiswa::where('nim', '2026TH050')->firstOrFail();
    expect($m->kelurahan)->toBe('Wagom')->and($m->kode_pos)->toBe('98611')->and($m->pekerjaan_ibu)->toBe('Guru')
        ->and((int) $m->dosen_wali_id)->toBe($d['dosen']->id)->and($m->user->hasRole('mahasiswa'))->toBeTrue();

    // impor ulang hanya mengubah nama; sel kosong tidak menghapus NIK & dosen wali
    $this->actingAs($d['admin'])->post(route('admin.mahasiswa.import'), ['file' => csvImpor($judul, [
        ['2026TH050', 'Paulus Baru Sekali', 'TH', '', '', '', '', '', '', ''],
    ])])->assertSessionHas('success');

    $m->refresh();
    expect($m->nama_lengkap)->toBe('Paulus Baru Sekali')->and($m->nik)->toBe('9201010101060050')->and((int) $m->dosen_wali_id)->toBe($d['dosen']->id)
        ->and(User::where('email', 'like', '2026TH050%')->count())->toBe(1);
});

test('impor mata kuliah menolak prodi tidak dikenal dan tidak menempel ke kurikulum lain', function () {
    $d = $this->seedAkademik();
    $judul = ['Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Program Studi', 'Dosen Pengampu', 'Kurikulum ID', 'Hari', 'Jam Mulai', 'Jam Selesai'];

    $this->actingAs($d['admin'])->post(route('admin.mata-kuliah.import'), ['file' => csvImpor($judul, [
        ['XX101', 'Liturgika', '2', '1', 'Prodi Fiktif', '', '', '', '', ''],
    ])])->assertSessionHas('error', fn ($pesan) => str_contains($pesan, 'Prodi Fiktif'));
    expect(MataKuliah::where('kode_mk', 'XX101')->exists())->toBeFalse();

    $this->actingAs($d['admin'])->post(route('admin.mata-kuliah.import'), ['file' => csvImpor($judul, [
        ['PK205', 'Katekese', '2', '3', 'PK', 'Dosen A', '', 'rabu', '10:00', '12:00'],
    ])])->assertSessionHas('success');
    $mk = MataKuliah::where('kode_mk', 'PK205')->firstOrFail();
    expect((int) $mk->kurikulum_id)->toBe(Kurikulum::where('program_studi_id', $d['prodiLain']->id)->value('id'))
        ->and($mk->jadwals()->first()->hari)->toBe('Rabu');
});

test('template impor dapat diunduh', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['admin'])->get(route('admin.mahasiswa.import.template'))->assertOk();
    $this->actingAs($d['admin'])->get(route('admin.dosen.import.template'))->assertOk();
    $this->actingAs($d['admin'])->get(route('admin.mata-kuliah.import.template'))->assertOk();
});

// ---------------------------------------------------------------- Profil & PMB

test('mahasiswa tidak dapat mengganti nama resmi lewat profil', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['mUser'])->patch(route('profile.update'), ['name' => 'Nama Palsu', 'email' => 'mhs@x.test'])->assertRedirect(route('profile.edit'));
    expect($d['mUser']->fresh()->name)->toBe('Mhs A');
});

test('gelombang PMB gratis langsung lunas dan nomor registrasi memakai tahun PMB', function () {
    $d = $this->seedAkademik();
    $p = PmbPeriod::create(['nama_gelombang' => 'Jalur Undangan', 'tahun_pmb' => 2031, 'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addWeek(),
        'biaya_pendaftaran' => 0, 'is_active' => true, 'jenis_ujian' => 'offline']);

    $this->post(route('pmb.register.store'), ['name' => 'Calon', 'email' => 'c@x.test', 'no_hp' => '08', 'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123',
        'pmb_period_id' => $p->id, 'pilihan_prodi_1_id' => $d['prodi']->id])->assertRedirect(route('dashboard'));

    $u = User::where('email', 'c@x.test')->firstOrFail();
    expect(Pembayaran::where('user_id', $u->id)->value('status'))->toBe('lunas')
        ->and($u->camaba->no_pendaftaran)->toStartWith('REG-2031-');
});
