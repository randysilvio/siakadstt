<?php

use App\Models\Koleksi;
use App\Models\Pembayaran;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

// Modul Keuangan & Perpustakaan sengaja tidak dapat diakses Super Admin.

test('super admin tidak dapat membuka modul keuangan', function () {
    $d = $this->seedAkademik();
    $tagihan = Pembayaran::create(['user_id' => $d['mUser']->id, 'mahasiswa_id' => $d['mhs']->id, 'semester' => 'X',
        'jenis_pembayaran' => 'spp', 'jumlah' => 100, 'status' => 'menunggu_konfirmasi']);

    foreach (['/pembayaran', '/pembayaran/create', '/pembayaran/generate', '/pembayaran/cetak', '/pembayaran/' . $tagihan->id . '/edit'] as $url) {
        $this->actingAs($d['admin'])->get($url)->assertRedirect(route('dashboard'));
    }

    $this->actingAs($d['admin'])->patch(route('pembayaran.lunas', $tagihan))->assertRedirect(route('dashboard'));
    $this->actingAs($d['admin'])->delete(route('pembayaran.destroy', $tagihan))->assertRedirect(route('dashboard'));

    expect($tagihan->fresh()->status)->toBe('menunggu_konfirmasi');
});

test('super admin tidak dapat membuka modul perpustakaan', function () {
    $d = $this->seedAkademik();
    $buku = Koleksi::create(['judul' => 'Dogmatika', 'pengarang' => 'X', 'penerbit' => 'Y', 'tahun_terbit' => 2020,
        'jumlah_stok' => 1, 'jumlah_tersedia' => 1, 'lokasi_rak' => 'A1']);

    foreach (['/perpustakaan', '/perpustakaan/koleksi', '/perpustakaan/peminjaman', '/perpustakaan/pengembalian'] as $url) {
        $this->actingAs($d['admin'])->get($url)->assertRedirect(route('dashboard'));
    }

    $this->actingAs($d['admin'])->delete(route('perpustakaan.koleksi.destroy', $buku))->assertRedirect(route('dashboard'));
    expect(Koleksi::find($buku->id))->not->toBeNull();
});

test('dosen dengan otoritas keuangan tetap dapat membuka modul keuangan', function () {
    $d = $this->seedAkademik();
    $d['dosen']->update(['is_keuangan' => true]);
    $this->actingAs($d['dUser']->fresh())->get('/pembayaran')->assertOk();
});

test('admin tetap dapat melihat bukti bayar formulir PMB untuk verifikasi pendaftar', function () {
    \Illuminate\Support\Facades\Storage::fake('local');
    $d = $this->seedAkademik();
    $camabaUser = $this->buatUser('Calon', 'calon@x.test', ['camaba']);
    \Illuminate\Support\Facades\Storage::disk('local')->put('private/bukti-bayar-pmb/a.jpg', 'isi');
    $formulir = Pembayaran::create(['user_id' => $camabaUser->id, 'jenis_pembayaran' => 'formulir_pmb', 'jumlah' => 100,
        'status' => 'menunggu_konfirmasi', 'semester' => 'PMB', 'bukti_bayar' => 'private/bukti-bayar-pmb/a.jpg']);

    $this->actingAs($d['admin'])->get(route('berkas.unduh', ['jenis' => 'bukti-bayar', 'id' => $formulir->id]))->assertOk();
});

test('menu admin hanya memuat satu tautan kalender akademik', function () {
    $d = $this->seedAkademik();
    $html = $this->actingAs($d['admin'])->get('/admin/kalender')->assertOk()->getContent();

    expect(substr_count($html, '>Kalender Akademik</a>'))->toBe(1)
        ->and($html)->not->toContain('Kelola Agenda Kalender')
        ->and($html)->toContain(route('kalender.halaman'))     // tombol "Lihat Kalender" di halaman manajemen
        ->and($html)->toContain(route('admin.kalender.create'));
});
