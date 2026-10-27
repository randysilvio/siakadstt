<?php

use App\Models\Pembayaran;
use App\Models\Pengumuman;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

// Uji asap: halaman utama tiap peran dirender tanpa error setelah perubahan logika.

test('halaman mahasiswa dapat dibuka', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    Pengumuman::create(['judul' => 'Info', 'slug' => 'info-x', 'kategori' => 'pengumuman', 'konten' => '<p>x</p>', 'target_role' => 'mahasiswa']);

    foreach (['/dashboard', '/krs', '/khs', '/transkrip', '/riwayat-pembayaran', '/evaluasi', '/layanan-surat', '/bimbingan-akademik', '/verum', '/notifikasi', '/profile'] as $url) {
        $this->actingAs($d['mUser'])->get($url)->assertOk();
    }
});

test('halaman dosen dan kaprodi dapat dibuka', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    $d['prodi']->update(['kaprodi_dosen_id' => $d['dosen']->id]);

    foreach (['/dashboard', '/dosen/dashboard', '/perwalian', '/perwalian/' . $d['mhs']->id, '/kaprodi/dashboard',
              '/kaprodi/validasi-krs/' . $d['mhs']->id, '/nilai/' . $d['mk']->id, '/verum', '/verum/kelas/create',
              '/bimbingan-akademik', '/dosen/tridharma'] as $url) {
        $this->actingAs($d['dUser'])->get($url)->assertOk();
    }
});

test('halaman admin dapat dibuka', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    Pembayaran::create(['user_id' => $d['mUser']->id, 'mahasiswa_id' => $d['mhs']->id, 'semester' => 'X',
        'jenis_pembayaran' => 'spp', 'jumlah' => 100, 'status' => 'menunggu_konfirmasi']);
    $p = Pengumuman::create(['judul' => 'Info', 'slug' => 'info-x', 'kategori' => 'berita', 'konten' => '<p>x</p>', 'target_role' => 'semua']);

    foreach (['/dashboard', '/admin/mahasiswa', '/admin/mahasiswa/' . $d['mhs']->id . '/edit', '/admin/user', '/admin/user/' . $d['mUser']->id . '/edit',
              '/admin/mata-kuliah', '/admin/kurikulum', '/admin/tahun-akademik', '/admin/pengumuman',
              '/admin/pengumuman/' . $p->slug, '/admin/pengumuman/' . $p->slug . '/edit', '/admin/pengaturan', '/admin/feeder', '/admin/pmb',
              '/admin/kalender'] as $url) {
        $this->actingAs($d['admin'])->get($url)->assertOk();
    }
});

test('halaman staf keuangan dan pustakawan dapat dibuka', function () {
    $d = $this->seedAkademik();
    Pembayaran::create(['user_id' => $d['mUser']->id, 'mahasiswa_id' => $d['mhs']->id, 'semester' => 'X',
        'jenis_pembayaran' => 'spp', 'jumlah' => 100, 'status' => 'menunggu_konfirmasi']);
    $keu = $this->buatUser('Staf Keu', 'keu@x.test', ['keuangan']);
    $pus = $this->buatUser('Pustakawan', 'pus@x.test', ['pustakawan']);

    foreach (['/dashboard', '/pembayaran', '/pembayaran/create', '/pembayaran/generate'] as $url) {
        $this->actingAs($keu)->get($url)->assertOk();
    }
    foreach (['/dashboard', '/perpustakaan', '/perpustakaan/koleksi', '/perpustakaan/peminjaman', '/perpustakaan/pengembalian'] as $url) {
        $this->actingAs($pus)->get($url)->assertOk();
    }
});
