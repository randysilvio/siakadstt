<?php

use App\Models\LokasiKerja;
use App\Models\Pembayaran;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

test('login API memberi token dan profil berisi peran', function () {
    $d = $this->seedAkademik();
    $token = $this->postJson('/api/login', ['email' => 'mhs@x.test', 'password' => 'password123'])->assertOk()->json('token');
    $this->withToken($token)->getJson('/api/user')->assertOk()
        ->assertJsonPath('role', 'mahasiswa')->assertJsonPath('is_kaprodi', false)->assertJsonPath('roles.0', 'mahasiswa');
    $this->getJson('/api/check-version')->assertOk()->assertJsonStructure(['min_version', 'download_url']);
});

test('form KRS API memuat semester, kelas terpilih & kursi terisi; dosen wali melihat detail lalu menyetujui', function () {
    $d = $this->seedAkademik();
    Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1, 'semester' => 'x', 'status' => 'lunas', 'jenis_pembayaran' => 'spp']);
    $this->actingAs($d['mUser'], 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'B']])->assertOk();

    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/krs/form')->assertOk()
        ->assertJsonPath('kelas_diambil.' . $d['mk']->id, 'B')->assertJsonPath('terisi.' . $d['mk']->id . '.B', 1)
        ->assertJsonPath('status_krs', 'Menunggu Persetujuan');

    $this->actingAs($d['dUser'], 'sanctum')->getJson("/api/krs/validasi/{$d['mhs']->id}")->assertOk()
        ->assertJsonPath('total_sks', 3)->assertJsonPath('mata_kuliah.0.kelas', 'B');
    $lain = $this->buatUser('Lain', 'lain@x.test', ['dosen']);
    \App\Models\Dosen::create(['user_id' => $lain->id, 'nidn' => '7', 'nama_lengkap' => 'Lain', 'jenis_pengajar' => 'Dosen']);
    $this->actingAs($lain, 'sanctum')->getJson("/api/krs/validasi/{$d['mhs']->id}")->assertForbidden();

    $this->actingAs($d['dUser'], 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Setujui'])->assertOk();
    expect($d['mhs']->fresh()->krsDisetujuiPada($d['ta']->id))->toBeTrue();
});

test('KHS API memuat nama semester', function () {
    $d = $this->seedAkademik();
    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nilai' => 'A']);
    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/khs-mahasiswa')->assertOk()
        ->assertJsonPath('khs.0.semester', $d['ta']->nama_semester);
});

test('absensi menolak lokasi palsu dari aplikasi', function () {
    $d = $this->seedAkademik();
    \Illuminate\Support\Facades\Storage::fake('local');
    LokasiKerja::create(['nama_lokasi' => 'Kampus', 'latitude' => -2.9, 'longitude' => 132.3, 'radius_toleransi_meter' => 100]);

    $this->actingAs($d['dUser'], 'sanctum')->postJson('/api/absensi/check-in', [
        'latitude' => -2.9, 'longitude' => 132.3, 'is_mocked' => 1, 'foto_check_in' => UploadedFile::fake()->image('s.jpg'),
    ])->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'lokasi palsu'));

    $this->actingAs($d['dUser'], 'sanctum')->postJson('/api/absensi/check-in', [
        'latitude' => -2.9, 'longitude' => 132.3, 'is_mocked' => 0, 'foto_check_in' => UploadedFile::fake()->image('s.jpg'),
    ])->assertCreated();
});

test('token API kedaluwarsa sesuai konfigurasi', function () {
    expect(config('sanctum.expiration'))->toBe(60 * 24 * 90);
});

test('mahasiswa mengunggah bukti bayar dari aplikasi; tagihan orang lain & yang lunas ditolak', function () {
    \Illuminate\Support\Facades\Storage::fake('local');
    $d = $this->seedAkademik();
    $tagihan = Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1500000, 'semester' => 'Ganjil 2025/2026', 'status' => 'belum_lunas', 'jenis_pembayaran' => 'spp', 'keterangan' => 'Cicilan 1']);

    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/riwayat-pembayaran-mahasiswa')->assertOk()
        ->assertJsonPath('0.jenis', 'spp')->assertJsonPath('0.keterangan', 'Cicilan 1')->assertJsonPath('0.ada_bukti', false);

    $this->actingAs($d['mUser'], 'sanctum')->post("/api/pembayaran/{$tagihan->id}/bukti", ['bukti_bayar' => 'bukan-file'], ['Accept' => 'application/json'])
        ->assertStatus(422);

    $this->actingAs($d['mUser'], 'sanctum')
        ->post("/api/pembayaran/{$tagihan->id}/bukti", ['bukti_bayar' => UploadedFile::fake()->image('bukti.jpg', 800, 600)], ['Accept' => 'application/json'])
        ->assertOk()->assertJsonPath('status', 'menunggu_konfirmasi');
    expect($tagihan->fresh()->status)->toBe('menunggu_konfirmasi')->and($tagihan->fresh()->bukti_bayar)->not->toBeNull();

    $lain = $this->buatUser('Lain', 'lainm@x.test', ['mahasiswa']);
    \App\Models\Mahasiswa::create(['user_id' => $lain->id, 'nim' => '999', 'nama_lengkap' => 'Lain', 'program_studi_id' => $d['mhs']->program_studi_id, 'tahun_masuk' => 2024, 'status_mahasiswa' => 'Aktif']);
    $this->actingAs($lain, 'sanctum')
        ->post("/api/pembayaran/{$tagihan->id}/bukti", ['bukti_bayar' => UploadedFile::fake()->image('b.jpg')], ['Accept' => 'application/json'])
        ->assertForbidden();

    $tagihan->update(['status' => 'lunas']);
    $this->actingAs($d['mUser'], 'sanctum')
        ->post("/api/pembayaran/{$tagihan->id}/bukti", ['bukti_bayar' => UploadedFile::fake()->image('b.jpg')], ['Accept' => 'application/json'])
        ->assertStatus(422);
});
