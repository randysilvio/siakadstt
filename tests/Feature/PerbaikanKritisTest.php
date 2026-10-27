<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

// K6 — rute autentikasi tidak lagi tertangkap rute slug pengumuman
test('halaman lupa password dapat dibuka', function () {
    $this->get('/forgot-password')->assertOk();
});

test('slug berita publik tetap dapat dibuka', function () {
    \App\Models\Pengumuman::create(['judul' => 'Wisuda', 'slug' => 'wisuda-abc12', 'kategori' => 'berita', 'konten' => '<p>isi</p>', 'target_role' => 'semua']);
    $this->get('/wisuda-abc12')->assertOk();
});

// K3 — bukti bayar yang belum diverifikasi tidak membuka KRS
test('unggah bukti bayar tidak langsung membuka KRS', function () {
    Storage::fake('public');
    $d = $this->seedAkademik();
    $tagihan = Pembayaran::create([
        'user_id' => $d['mUser']->id, 'mahasiswa_id' => $d['mhs']->id, 'semester' => '2026/2027 Ganjil',
        'jenis_pembayaran' => 'spp', 'jumlah' => 1000000, 'status' => 'belum_lunas',
    ]);

    $this->actingAs($d['mUser'])->post(route('pembayaran.uploadBukti', $tagihan), [
        'bukti_bayar' => UploadedFile::fake()->image('bukti.jpg'),
    ]);
    expect($tagihan->fresh()->status)->toBe('menunggu_konfirmasi');

    $this->actingAs($d['mUser'])->get('/krs')->assertRedirect(route('pembayaran.riwayat'));

    $keu = $this->buatUser('Staf Keu', 'keu@x.test', ['keuangan']);
    $this->actingAs($keu)->patch(route('pembayaran.lunas', $tagihan));
    $this->actingAs($d['mUser'])->get('/krs')->assertOk();
});

test('keuangan dapat menolak bukti bayar', function () {
    Storage::fake('public');
    $d = $this->seedAkademik();
    $tagihan = Pembayaran::create([
        'user_id' => $d['mUser']->id, 'mahasiswa_id' => $d['mhs']->id, 'semester' => '2026/2027 Ganjil',
        'jenis_pembayaran' => 'spp', 'jumlah' => 1000000, 'status' => 'menunggu_konfirmasi',
    ]);

    $keu = $this->buatUser('Staf Keu', 'keu@x.test', ['keuangan']);
    $this->actingAs($keu)
        ->patch(route('pembayaran.tolakBukti', $tagihan), ['alasan' => 'Nominal tidak sesuai'])
        ->assertSessionHas('success');

    $tagihan->refresh();
    expect($tagihan->status)->toBe('belum_lunas')
        ->and($tagihan->keterangan)->toContain('Nominal tidak sesuai')
        ->and($d['mUser']->notifications()->count())->toBe(1);
});

// K4 / K5 — buku nilai
test('input kehadiran saja tidak menghasilkan nilai huruf', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    $id = $d['mhs']->id;

    $this->actingAs($d['dUser'])->post('/nilai', [
        'mata_kuliah_id' => $d['mk']->id,
        'peserta' => [$id],
        'nilai_kehadiran' => [$id => 100],
        'nilai' => [$id => ''],
        'huruf_manual' => [$id => '0'],
    ]);

    $row = DB::table('mahasiswa_mata_kuliah')->where('mahasiswa_id', $id)->first();
    expect($row->nilai)->toBeNull()
        ->and((float) $row->nilai_kehadiran)->toBe(100.0)
        ->and($d['mhs']->fresh()->hitungIpk())->toBe(0.0);
});

test('huruf otomatis dihitung setelah UTS/UAS diisi', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    $id = $d['mhs']->id;

    $this->actingAs($d['dUser'])->post('/nilai', [
        'mata_kuliah_id' => $d['mk']->id,
        'peserta' => [$id],
        'nilai_kehadiran' => [$id => 100], 'nilai_tugas' => [$id => 90],
        'nilai_uts' => [$id => 85], 'nilai_uas' => [$id => 80],
        'huruf_manual' => [$id => '0'],
    ]);

    expect(DB::table('mahasiswa_mata_kuliah')->where('mahasiswa_id', $id)->value('nilai'))->toBe('A');
});

test('huruf manual dosen tidak ditimpa konversi', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    $id = $d['mhs']->id;

    $this->actingAs($d['dUser'])->post('/nilai', [
        'mata_kuliah_id' => $d['mk']->id,
        'peserta' => [$id],
        'nilai_kehadiran' => [$id => 100], 'nilai_tugas' => [$id => 90],
        'nilai' => [$id => 'A'], 'huruf_manual' => [$id => '1'],
    ]);

    $row = DB::table('mahasiswa_mata_kuliah')->where('mahasiswa_id', $id)->first();
    expect($row->nilai)->toBe('A')->and((bool) $row->nilai_manual)->toBeTrue();
});

test('peserta yang tidak ada di form tidak ikut dikosongkan', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    DB::table('mahasiswa_mata_kuliah')->where('mahasiswa_id', $d['mhs']->id)->update(['nilai' => 'B', 'nilai_uts' => 75]);

    $this->actingAs($d['dUser'])->post('/nilai', ['mata_kuliah_id' => $d['mk']->id, 'peserta' => []]);

    expect(DB::table('mahasiswa_mata_kuliah')->where('mahasiswa_id', $d['mhs']->id)->value('nilai'))->toBe('B');
});

// K1 / K2 / T11 — riwayat akademik tidak bisa terhapus
test('mata kuliah yang punya riwayat nilai tidak dapat dihapus', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);

    $this->actingAs($d['admin'])->delete(route('admin.mata-kuliah.destroy', $d['mk']))->assertSessionHas('error');

    expect(DB::table('mahasiswa_mata_kuliah')->where('mahasiswa_id', $d['mhs']->id)->count())->toBe(1);
});

test('mata kuliah tanpa riwayat tetap dapat dihapus', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['admin'])->delete(route('admin.mata-kuliah.destroy', $d['mkLain']))->assertSessionHas('success');
    expect(\App\Models\MataKuliah::find($d['mkLain']->id))->toBeNull();
});

test('mahasiswa tidak dapat menghapus akunnya sendiri', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['mUser'])->delete('/profile', ['password' => 'password123'])->assertSessionHas('error');
    expect(Mahasiswa::find($d['mhs']->id))->not->toBeNull()
        ->and($d['mUser']->fresh())->not->toBeNull();
});

test('admin tidak dapat menghapus mahasiswa yang punya riwayat KRS', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    $this->actingAs($d['admin'])->delete(route('admin.mahasiswa.destroy', $d['mhs']))->assertSessionHas('error');
    $this->actingAs($d['admin'])->delete(route('admin.user.destroy', $d['mUser']))->assertSessionHas('error');
    expect(Mahasiswa::find($d['mhs']->id))->not->toBeNull();
});

test('hapus dosen tanpa keterkaitan menghapus record dosen dan akunnya', function () {
    $d = $this->seedAkademik();
    $u = $this->buatUser('Dosen B', 'dosenb@x.test', ['dosen']);
    $dosenB = Dosen::create(['user_id' => $u->id, 'nidn' => '222', 'nama_lengkap' => 'Dosen B', 'jenis_pengajar' => 'Dosen']);

    $this->actingAs($d['admin'])->delete(route('admin.dosen.destroy', $dosenB))->assertSessionHas('success');

    expect(Dosen::find($dosenB->id))->toBeNull()->and($u->fresh())->toBeNull();
});

test('hapus dosen yang masih mengampu mata kuliah ditolak', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['admin'])->delete(route('admin.dosen.destroy', $d['dosen']))->assertSessionHas('error');
    expect(Dosen::find($d['dosen']->id))->not->toBeNull();
});
