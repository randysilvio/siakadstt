<?php

use App\Support\InfoAturan;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

test('setiap aturan lengkap dan halaman rangkuman hanya untuk admin', function () {
    $d = $this->seedAkademik();
    foreach (InfoAturan::semua() as $kunci => $a) {
        expect($a)->toHaveKeys(['modul', 'judul', 'isi', 'dasar', 'jenis', 'halaman'])
            ->and(array_key_exists($a['jenis'], InfoAturan::JENIS))->toBeTrue("jenis {$kunci}");
    }
    $this->actingAs($d['admin'])->get(route('admin.aturan.index'))->assertOk()->assertSee('Batas SKS per semester')->assertSee('PERLU KONFIRMASI');
    $this->actingAs($d['mUser'])->get(route('admin.aturan.index'))->assertRedirect(route('dashboard'));
});

test('ikon penjelasan tampil di halaman yang menghitung angka', function () {
    $d = $this->seedAkademik();
    \App\Models\Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1, 'semester' => 'x', 'status' => 'lunas', 'jenis_pembayaran' => 'spp']);

    $this->actingAs($d['mUser'])->get(route('krs.index'))->assertOk()->assertSee('Batas SKS per semester')->assertSee('info-aturan', false);
    $this->actingAs($d['dUser'])->get(route('dosen.tridharma.index'))->assertOk()->assertSee('Estimasi angka kredit (KUM)');
    $this->actingAs($d['dUser'])->get(route('nilai.show', $d['mk']))->assertOk()->assertSee('Nilai akhir &amp; huruf mutu', false);
});
