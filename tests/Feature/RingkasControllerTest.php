<?php

use App\Models\Pembayaran;
use App\Services\JadwalService;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

test('jadwal mahasiswa hanya memuat rombel yang diambil, terurut hari lalu jam', function () {
    $d = $this->seedAkademik();
    $d['mk']->jadwals()->create(['nama_kelas' => 'B', 'hari' => 'Senin', 'jam_mulai' => '13:00', 'jam_selesai' => '15:00', 'kuota' => 40]);
    $this->ambilDanSetujui($d, $d['mk'], 'B');

    $jadwal = app(JadwalService::class)->jadwalMahasiswa($d['mhs'], $d['ta']->id);

    expect($jadwal->pluck('hari')->all())->toBe(['Senin', 'Rabu'])
        ->and($jadwal->pluck('nama_kelas')->unique()->all())->toBe(['B']);

    $this->actingAs($d['mUser'])->getJson('/api/jadwal-kuliah')->assertOk()->assertJsonCount(2);
});

test('verifikasi lunas oleh keuangan mencatat verifikator dan admin tetap terkunci', function () {
    $d = $this->seedAkademik();
    $keu = $this->buatUser('Keuangan', 'keu@x.test', ['keuangan']);
    $tagihan = Pembayaran::create(['user_id' => $d['mUser']->id, 'mahasiswa_id' => $d['mhs']->id, 'jenis_pembayaran' => 'spp',
        'jumlah' => 1000000, 'status' => 'menunggu_konfirmasi', 'semester' => '2026/2027 Ganjil']);

    $this->actingAs($d['admin'])->get(route('pembayaran.index'))->assertRedirect(route('dashboard'));

    $this->actingAs($keu)->get(route('pembayaran.index', ['status' => 'menunggu_konfirmasi']))->assertOk();
    $this->actingAs($keu)->patch(route('pembayaran.lunas', $tagihan))->assertSessionHas('success');

    $tagihan->refresh();
    expect($tagihan->status)->toBe('lunas')->and((int) $tagihan->diverifikasi_oleh)->toBe($keu->id);
});
