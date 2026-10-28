<?php

use App\Http\Controllers\Admin\FeederExportController;
use App\Models\BimbinganTugasAkhir;
use App\Models\Camaba;
use App\Models\DokumenTerbit;
use App\Models\Dosen;
use App\Models\KrsPersetujuan;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\PmbPeriod;
use App\Support\BerkasPrivat;
use App\Support\HtmlSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

// S1 — sanitizer
test('sanitizer menolak javascript: yang di-encode dan atribut event', function () {
    $bersih = HtmlSanitizer::clean('<a href="&#106;avascript:alert(1)">x</a><p onclick="x()">ok</p><script>alert(1)</script>');
    expect($bersih)->not->toContain('javascript')
        ->not->toContain('onclick')
        ->not->toContain('<script')
        ->toContain('<p>ok</p>');
});

test('sanitizer mempertahankan format teks dan tautan aman', function () {
    $html = '<div><strong>Tebal</strong> <a href="https://sttgpipapua.ac.id">situs</a></div>';
    expect(HtmlSanitizer::clean($html))->toContain('<strong>Tebal</strong>')->toContain('href="https://sttgpipapua.ac.id"');
});

// S12 — berkas privat
test('bukti bayar baru disimpan privat dan hanya bisa dibuka pemilik atau keuangan', function () {
    Storage::fake('local');
    Storage::fake('public');
    $d = $this->seedAkademik();
    $tagihan = Pembayaran::create(['user_id' => $d['mUser']->id, 'mahasiswa_id' => $d['mhs']->id, 'semester' => 'X',
        'jenis_pembayaran' => 'spp', 'jumlah' => 100, 'status' => 'belum_lunas']);

    $this->actingAs($d['mUser'])->post(route('pembayaran.uploadBukti', $tagihan), ['bukti_bayar' => UploadedFile::fake()->image('b.jpg')]);
    $path = $tagihan->fresh()->bukti_bayar;

    expect($path)->toStartWith('private/');
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);

    $url = route('berkas.unduh', ['jenis' => 'bukti-bayar', 'id' => $tagihan->id]);
    $keu = $this->buatUser('Staf Keu', 'keu@x.test', ['keuangan']);
    $this->actingAs($d['mUser'])->get($url)->assertOk();
    $this->actingAs($keu)->get($url)->assertOk();
    $this->actingAs($d['admin'])->get($url)->assertForbidden();   // Super Admin tidak mengakses modul Keuangan
    $this->actingAs($d['dUser'])->get($url)->assertForbidden();
});

test('berkas lama di disk public tetap terbaca lalu dapat dipindahkan', function () {
    Storage::fake('local');
    Storage::fake('public');
    $d = $this->seedAkademik();
    Storage::disk('public')->put('bukti-bayar-spp/lama.jpg', 'isi');
    $tagihan = Pembayaran::create(['user_id' => $d['mUser']->id, 'mahasiswa_id' => $d['mhs']->id, 'semester' => 'X',
        'jenis_pembayaran' => 'spp', 'jumlah' => 100, 'status' => 'menunggu_konfirmasi', 'bukti_bayar' => 'bukti-bayar-spp/lama.jpg']);

    $keu = $this->buatUser('Staf Keu', 'keu@x.test', ['keuangan']);
    $this->actingAs($keu)->get(route('berkas.unduh', ['jenis' => 'bukti-bayar', 'id' => $tagihan->id]))->assertOk();

    $this->artisan('siakad:amankan-berkas')->assertSuccessful();

    expect($tagihan->fresh()->bukti_bayar)->toBe('private/bukti-bayar-spp/lama.jpg');
    Storage::disk('local')->assertExists('private/bukti-bayar-spp/lama.jpg');
    Storage::disk('public')->assertMissing('bukti-bayar-spp/lama.jpg');
});

test('upload file berbahaya seperti php atau html ditolak', function () {
    $file = UploadedFile::fake()->create('payload.php', 10, 'text/x-php');
    $folder = 'bukti-bayar-spp';

    expect(fn () => BerkasPrivat::simpan($file, $folder))->toThrow(\RuntimeException::class, 'Tipe berkas tidak diizinkan untuk diunggah.');

    $html = UploadedFile::fake()->create('payload.html', 10, 'text/html');
    expect(fn () => BerkasPrivat::simpan($html, $folder))->toThrow(\RuntimeException::class, 'Tipe berkas tidak diizinkan untuk diunggah.');
});

// S7 — verifikasi terikat isi
test('cetak KHS menerbitkan kode yang memuat isi dokumen', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    \Illuminate\Support\Facades\DB::table('mahasiswa_mata_kuliah')->update(['nilai' => 'A', 'nilai_uts' => 90]);

    $this->actingAs($d['mUser'])->get(route('khs.cetak', ['tahun_akademik_id' => $d['ta']->id]))->assertOk();

    $dok = DokumenTerbit::where('jenis', 'KHS')->first();
    expect($dok)->not->toBeNull()->and($dok->utuh())->toBeTrue();

    $this->get(route('verifikasi.dokumen', $dok->kode))
        ->assertOk()->assertSee('Dogmatika')->assertSee('4.00');
});

// S4 / S5 — PMB
test('camaba tidak lulus tidak dapat diluluskan dan NIK tidak dipalsukan', function () {
    $d = $this->seedAkademik();
    $periode = PmbPeriod::create(['nama_gelombang' => 'G1', 'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addDay(),
        'biaya_pendaftaran' => 100, 'is_active' => true, 'jenis_ujian' => 'offline']);
    $u = $this->buatUser('Calon', 'calon@x.test', ['camaba']);
    $camaba = Camaba::create(['user_id' => $u->id, 'pmb_period_id' => $periode->id, 'no_pendaftaran' => 'REG-1',
        'pilihan_prodi_1_id' => $d['prodi']->id, 'tempat_lahir' => 'Fakfak', 'tanggal_lahir' => '2007-01-01',
        'jenis_kelamin' => 'L', 'status_pendaftaran' => 'tidak_lulus']);
    Pembayaran::create(['user_id' => $u->id, 'jenis_pembayaran' => 'formulir_pmb', 'jumlah' => 100, 'status' => 'lunas', 'semester' => 'PMB']);

    $this->actingAs($d['admin'])->put(route('admin.pmb.approve', $camaba))->assertSessionHas('error');
    expect(Mahasiswa::where('user_id', $u->id)->exists())->toBeFalse();

    $camaba->update(['status_pendaftaran' => 'menunggu_verifikasi']);
    $this->actingAs($d['admin'])->put(route('admin.pmb.approve', $camaba));

    $mhs = Mahasiswa::where('user_id', $u->id)->first();
    expect($mhs)->not->toBeNull()->and($mhs->nik)->toBeNull();
});

test('validasi NIK Feeder menolak NIK buatan dari timestamp', function () {
    expect(FeederExportController::nikValid('9102012345678901'))->toBeTrue()
        ->and(FeederExportController::nikValid('2026102814300512'))->toBeFalse()
        ->and(FeederExportController::nikValid('123'))->toBeFalse();
});

// S9 — bimbingan
test('dosen pembimbing tidak dapat menetapkan kelulusan atau mengganti pembimbing', function () {
    $d = $this->seedAkademik();
    $b = BimbinganTugasAkhir::create(['mahasiswa_id' => $d['mhs']->id, 'jenis_bimbingan' => 'Skripsi / Tugas Akhir', 'judul' => 'Judul',
        'pembimbing_1_id' => $d['dosen']->id, 'status' => 'Disetujui / Aktif Bimbingan', 'catatan_kaprodi' => 'catatan lama']);
    $lain = Dosen::create(['nidn' => '999', 'nama_lengkap' => 'Lain', 'jenis_pengajar' => 'Dosen']);

    $this->actingAs($d['dUser'])->patch(route('bimbingan.updateStatus', $b), ['status' => 'Selesai / Lulus', 'nilai_huruf' => 'A'])
        ->assertSessionHas('error');

    $this->actingAs($d['dUser'])->patch(route('bimbingan.updateStatus', $b), ['status' => 'Siap Sidang', 'pembimbing_1_id' => $lain->id]);
    $b->refresh();
    expect($b->status)->toBe('Siap Sidang')
        ->and((int) $b->pembimbing_1_id)->toBe($d['dosen']->id)
        ->and($b->catatan_kaprodi)->toBe('catatan lama');
});

test('mahasiswa tidak dapat mengajukan dua judul skripsi aktif', function () {
    $d = $this->seedAkademik();
    $data = ['jenis_bimbingan' => 'Skripsi / Tugas Akhir', 'judul' => 'Judul A'];
    $this->actingAs($d['mUser'])->post(route('bimbingan.store'), $data)->assertSessionHas('success');
    $this->actingAs($d['mUser'])->post(route('bimbingan.store'), ['judul' => 'Judul B'] + $data)->assertSessionHas('error');
    expect(BimbinganTugasAkhir::count())->toBe(1);
});

// S11 — chatbot
test('pertanyaan data pribadi dijawab lokal tanpa memanggil API AI', function () {
    config(['services.gemini.api_key' => 'kunci-uji']);
    Http::fake();
    $d = $this->seedAkademik();

    $this->actingAs($d['mUser'])->postJson(route('chatbot.handle'), ['message' => 'Berapa IPK saya?'])
        ->assertOk()->assertJsonFragment([]);

    Http::assertNothingSent();
});

// S13 — otoritas keuangan dosen
test('dosen dengan otoritas keuangan dapat membuka modul keuangan', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['dUser'])->get(route('pembayaran.index'))->assertRedirect(route('dashboard'));

    $d['dosen']->update(['is_keuangan' => true]);
    $this->actingAs($d['dUser']->fresh())->get(route('pembayaran.index'))->assertOk();
});

// S8 — status mahasiswa baku
test('status mahasiswa di luar daftar baku ditolak', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['admin'])->put(route('admin.mahasiswa.update', $d['mhs']), [
        'nim' => $d['mhs']->nim, 'nik' => '9102012345678901', 'nama_lengkap' => 'Mhs A', 'email' => 'mhs@x.test',
        'program_studi_id' => $d['prodi']->id, 'tahun_masuk' => 2026, 'nama_ibu_kandung' => 'Ibu', 'status_mahasiswa' => 'aktif ',
    ])->assertSessionHasErrors('status_mahasiswa');
});

// R1 — tanggal bahasa Indonesia
test('tanggal dokumen memakai bahasa Indonesia', function () {
    expect(\Carbon\Carbon::parse('2026-10-28')->translatedFormat('d F Y'))->toBe('28 Oktober 2026')
        ->and(__('validation.required', ['attribute' => 'nim']))->toContain('wajib diisi');
});

// R8 — impersonasi hanya POST
test('impersonasi tidak dapat dipicu lewat tautan GET', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['admin'])->get('/admin/user/' . $d['mUser']->id . '/impersonate')->assertStatus(405);
    $this->actingAs($d['admin'])->post(route('admin.user.impersonate', $d['mUser']->id))->assertRedirect(route('dashboard'));
});

test('berkas berisi skrip ditolak dengan pesan, bukan error server', function () {
    $d = $this->seedAkademik();
    \Illuminate\Support\Facades\Storage::fake('local');
    $tagihan = \App\Models\Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1, 'semester' => 'x', 'status' => 'belum_lunas', 'jenis_pembayaran' => 'spp']);
    $jahat = UploadedFile::fake()->createWithContent('bukti.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==') . '<script>alert(1)</script>');

    $this->actingAs($d['mUser'])->from(route('pembayaran.riwayat'))
        ->post(route('pembayaran.uploadBukti', $tagihan), ['bukti_bayar' => $jahat])
        ->assertRedirect(route('pembayaran.riwayat'))->assertSessionHas('error', 'Isi berkas berbahaya terdeteksi.');
    expect($tagihan->fresh()->status)->toBe('belum_lunas');
});
