<?php

use App\Models\Dosen;
use App\Models\KrsPersetujuan;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

/** Mahasiswa yang SPP-nya lunas dan siap mengisi KRS. */
function siapKrs($test): array
{
    $d = $test->seedAkademik();
    Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1, 'semester' => '2026/2027 Ganjil', 'status' => 'lunas', 'jenis_pembayaran' => 'spp']);
    return $d;
}

function ajukan($test, array $d, array $mkKelas)
{
    return $test->actingAs($d['mUser']->fresh())->post(route('krs.store'), [
        'mata_kuliahs' => array_keys($mkKelas), 'nama_kelas' => $mkKelas,
    ]);
}

// ---------------------------------------------------------------- Gerbang masuk

test('gerbang KRS: tagihan belum lunas / menunggu verifikasi menutup KRS di web dan aplikasi', function () {
    $d = $this->seedAkademik();
    $p = Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1, 'semester' => '2026/2027 Ganjil', 'status' => 'belum_lunas', 'jenis_pembayaran' => 'spp']);

    $this->actingAs($d['mUser'])->get(route('krs.index'))->assertRedirect(route('pembayaran.riwayat'));
    $this->actingAs($d['mUser'])->post(route('krs.store'), ['mata_kuliahs' => [$d['mk']->id]])->assertRedirect(route('pembayaran.riwayat'));
    $this->actingAs($d['mUser'], 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => [$d['mk']->id]])->assertForbidden();

    $p->update(['status' => 'menunggu_konfirmasi']);
    $this->actingAs($d['mUser'])->get(route('krs.index'))->assertRedirect(route('pembayaran.riwayat'));

    $p->update(['status' => 'dispensasi']);
    $this->actingAs($d['mUser'])->get(route('krs.index'))->assertOk();
    expect($d['mhs']->mataKuliahs()->count())->toBe(0);
});

test('gerbang KRS: di luar tanggal KRS atau saklar ditutup tidak bisa mengajukan, tetapi KRS yang ada tetap bisa dilihat', function () {
    $d = siapKrs($this);
    ajukan($this, $d, [$d['mk']->id => 'A'])->assertSessionHas('success');

    $d['ta']->update(['tanggal_mulai_krs' => now()->subMonth()->toDateString(), 'tanggal_selesai_krs' => now()->subDay()->toDateString()]);
    ajukan($this, $d, [$d['mk']->id => 'B'])->assertRedirect(route('dashboard'));
    $this->actingAs($d['mUser'])->get(route('krs.index'))->assertOk();
    expect($d['mhs']->mataKuliahs()->first()->pivot->nama_kelas)->toBe('A');

    $d['ta']->update(['tanggal_mulai_krs' => now()->subDay()->toDateString(), 'tanggal_selesai_krs' => now()->addMonth()->toDateString()]);
    Pengaturan::updateOrCreate(['key' => 'status_krs'], ['value' => 'tutup']);
    Pengaturan::clearCache();
    ajukan($this, $d, [$d['mk']->id => 'B'])->assertRedirect(route('dashboard'));
});

test('gerbang KRS: mahasiswa cuti/non-aktif tidak dapat mengisi KRS', function () {
    $d = siapKrs($this);
    $d['mhs']->update(['status_mahasiswa' => 'Cuti']);
    $this->actingAs($d['mUser']->fresh())->get(route('krs.index'))->assertRedirect(route('dashboard'));
    ajukan($this, $d, [$d['mk']->id => 'A'])->assertForbidden();
});

// ---------------------------------------------------------------- Aturan akademik

test('aturan: batas SKS, prasyarat, semester ganjil/genap, bentrok jadwal, dan kuota kelas', function () {
    $d = siapKrs($this);
    $kur = $d['mk']->kurikulum_id;
    $mk = fn ($kode, $sks, $smt, $hari, $mulai, $selesai, $kuota = 40) => tap(
        MataKuliah::create(['kurikulum_id' => $kur, 'kode_mk' => $kode, 'nama_mk' => "MK {$kode}", 'sks' => $sks, 'semester' => $smt, 'dosen_id' => $d['dosen']->id]),
        fn ($m) => $m->jadwals()->create(['nama_kelas' => 'A', 'hari' => $hari, 'jam_mulai' => $mulai, 'jam_selesai' => $selesai, 'kuota' => $kuota])
    );

    // melebihi batas 24 SKS (mahasiswa baru)
    $besar = $mk('TH190', 22, 1, 'Kamis', '08:00', '10:00');
    ajukan($this, $d, [$d['mk']->id => 'A', $besar->id => 'A'])->assertSessionHasErrors('mata_kuliahs');

    // semester genap tidak ditawarkan pada semester Ganjil
    $genap = $mk('TH102', 2, 2, 'Jumat', '08:00', '10:00');
    ajukan($this, $d, [$genap->id => 'A'])->assertSessionHasErrors('mata_kuliahs');

    // prasyarat belum lulus
    $lanjut = $mk('TH301', 2, 3, 'Jumat', '13:00', '15:00');
    $lanjut->prasyarats()->attach($d['mk']->id);
    ajukan($this, $d, [$lanjut->id => 'A'])->assertSessionHasErrors('mata_kuliahs');

    // bentrok: Dogmatika A Senin 08-10 vs MK lain Senin 09-11
    $bentrok = $mk('TH105', 2, 1, 'Senin', '09:00', '11:00');
    ajukan($this, $d, [$d['mk']->id => 'A', $bentrok->id => 'A'])->assertSessionHasErrors('mata_kuliahs');
    // kelas paralel B (Rabu) menghindari bentrok
    ajukan($this, $d, [$d['mk']->id => 'B', $bentrok->id => 'A'])->assertSessionHasNoErrors()->assertSessionHas('success');

    // kuota penuh
    $penuh = $mk('TH107', 2, 1, 'Sabtu', '08:00', '10:00', 1);
    $lain = Mahasiswa::create(['nim' => '2026TH099', 'nama_lengkap' => 'Lain', 'program_studi_id' => $d['prodi']->id, 'status_mahasiswa' => 'Aktif', 'tahun_masuk' => 2026, 'jenis_kelamin' => 'L', 'nama_ibu_kandung' => 'Ibu']);
    $lain->mataKuliahs()->attach($penuh->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
    ajukan($this, $d, [$d['mk']->id => 'B', $penuh->id => 'A'])->assertSessionHasErrors('mata_kuliahs');

    // pengajuan yang ditolak aturan tidak mengubah KRS yang sudah tersimpan
    expect($d['mhs']->mataKuliahs()->wherePivot('tahun_akademik_id', $d['ta']->id)->pluck('kode_mk')->sort()->values()->all())->toBe(['TH101', 'TH105']);
});

// ---------------------------------------------------------------- Persetujuan & penguncian

test('persetujuan: KRS dikunci setelah disetujui (web, hapus per MK, dan aplikasi)', function () {
    $d = siapKrs($this);
    ajukan($this, $d, [$d['mk']->id => 'A']);
    $this->actingAs($d['dUser'])->patch(route('perwalian.updateStatus', $d['mhs']), ['status_krs' => 'Disetujui'])->assertSessionHas('success');

    ajukan($this, $d, [$d['mk']->id => 'B'])->assertRedirect(route('krs.index'));
    $this->actingAs($d['mUser']->fresh())->delete(route('krs.destroy', $d['mk']->id));
    $this->actingAs($d['mUser']->fresh(), 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => []])->assertForbidden();
    $this->actingAs($d['dUser'])->delete(route('perwalian.krs.destroy', [$d['mhs']->id, $d['mk']->id]));

    $pivot = $d['mhs']->mataKuliahs()->wherePivot('tahun_akademik_id', $d['ta']->id)->get();
    expect($pivot)->toHaveCount(1)->and($pivot->first()->pivot->nama_kelas)->toBe('A')
        ->and($d['mhs']->fresh()->statusKrsPada($d['ta']->id))->toBe(KrsPersetujuan::DISETUJUI);
    $this->actingAs($d['mUser']->fresh())->get(route('krs.cetak.final'))->assertOk();
});

test('persetujuan: dosen lain tidak bisa memvalidasi; Kaprodi tidak membatalkan keputusan wali; wali dan admin dapat membuka kembali', function () {
    $d = siapKrs($this);
    $kUser = $this->buatUser('Kaprodi', 'kap@x.test', ['dosen']);
    $kap = Dosen::create(['user_id' => $kUser->id, 'nidn' => '555', 'nama_lengkap' => 'Kaprodi', 'jenis_pengajar' => 'Dosen']);
    $d['prodi']->update(['kaprodi_dosen_id' => $kap->id]);
    $asingUser = $this->buatUser('Dosen Lain', 'lain@x.test', ['dosen']);
    Dosen::create(['user_id' => $asingUser->id, 'nidn' => '666', 'nama_lengkap' => 'Dosen Lain', 'jenis_pengajar' => 'Dosen']);

    ajukan($this, $d, [$d['mk']->id => 'A']);

    // dosen yang bukan wali & bukan kaprodi
    $this->actingAs($asingUser)->patch(route('perwalian.updateStatus', $d['mhs']), ['status_krs' => 'Disetujui'])->assertNotFound();
    $this->actingAs($asingUser, 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Setujui'])->assertForbidden();
    expect($d['mhs']->fresh()->statusKrsPada($d['ta']->id))->toBe(KrsPersetujuan::MENUNGGU);

    // wali menyetujui; kaprodi tidak dapat menolak/membuka
    $this->actingAs($d['dUser'])->patch(route('perwalian.updateStatus', $d['mhs']), ['status_krs' => 'Disetujui']);
    $this->actingAs($kUser)->patch(route('kaprodi.krs.update', $d['mhs']), ['status_krs' => 'Ditolak'])->assertSessionHas('error');
    $this->actingAs($kUser, 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Tolak'])->assertStatus(422);
    expect($d['mhs']->fresh()->statusKrsPada($d['ta']->id))->toBe(KrsPersetujuan::DISETUJUI);

    // wali sendiri membuka kembali → mahasiswa bisa merevisi
    $this->actingAs($d['dUser'])->patch(route('perwalian.updateStatus', $d['mhs']), ['status_krs' => 'Menunggu Persetujuan'])->assertSessionHas('success');
    ajukan($this, $d, [$d['mk']->id => 'B'])->assertSessionHas('success');

    // kaprodi menyetujui (salah satu cukup); admin tetap bisa membuka
    $this->actingAs($kUser)->patch(route('kaprodi.krs.update', $d['mhs']), ['status_krs' => 'Disetujui'])->assertSessionHas('success');
    $this->actingAs($d['admin'], 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Tolak', 'catatan' => 'Salah rombel'])->assertOk();
    expect($d['mhs']->fresh()->statusKrsPada($d['ta']->id))->toBe(KrsPersetujuan::DITOLAK);
});

test('penolakan: catatan sampai ke mahasiswa, revisi kembali menunggu, nilai yang ada tidak hilang', function () {
    $d = siapKrs($this);
    ajukan($this, $d, [$d['mk']->id => 'A']);
    $d['mhs']->mataKuliahs()->updateExistingPivot($d['mk']->id, ['nilai_kehadiran' => 90]);

    $this->actingAs($d['dUser'])->patch(route('perwalian.updateStatus', $d['mhs']), ['status_krs' => 'Ditolak', 'catatan_kaprodi' => 'Ambil kelas B'])->assertSessionHas('success');
    expect($d['mUser']->notifications()->first()->data['message'])->toContain('Ambil kelas B');

    ajukan($this, $d, [$d['mk']->id => 'B'])->assertSessionHas('success');
    $pivot = $d['mhs']->mataKuliahs()->wherePivot('tahun_akademik_id', $d['ta']->id)->first()->pivot;
    expect($pivot->nama_kelas)->toBe('B')->and((float) $pivot->nilai_kehadiran)->toBe(90.0)
        ->and($d['mhs']->fresh()->statusKrsPada($d['ta']->id))->toBe(KrsPersetujuan::MENUNGGU);
});

test('aplikasi mobile: form & simpan KRS mengikuti aturan yang sama dengan web', function () {
    $d = siapKrs($this);
    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/krs/form')->assertOk()->assertJsonPath('max_sks', 24);
    $this->actingAs($d['mUser'], 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => [$d['mkLain']->id]])->assertStatus(422);
    $this->actingAs($d['mUser'], 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'B']])->assertOk();
    $this->actingAs($d['dUser'], 'sanctum')->getJson('/api/krs/perlu-validasi')->assertOk()->assertJsonFragment(['nim' => $d['mhs']->nim]);
});

test('pergantian semester: status KRS semester baru mulai dari awal, riwayat semester lama tetap', function () {
    $d = siapKrs($this);
    ajukan($this, $d, [$d['mk']->id => 'A']);
    $this->actingAs($d['dUser'])->patch(route('perwalian.updateStatus', $d['mhs']), ['status_krs' => 'Disetujui']);

    $baru = \App\Models\TahunAkademik::create(['tahun' => '2026/2027', 'semester' => 'Genap', 'is_active' => false,
        'tanggal_mulai_krs' => now()->subDay()->toDateString(), 'tanggal_selesai_krs' => now()->addMonth()->toDateString()]);
    $this->actingAs($d['admin'])->patch(route('admin.tahun-akademik.set-active', $baru))->assertSessionHas('success');

    $m = $d['mhs']->fresh();
    expect($m->status_krs)->toBe(KrsPersetujuan::BELUM)
        ->and($m->statusKrsPada($d['ta']->id))->toBe(KrsPersetujuan::DISETUJUI)
        ->and($m->statusKrsPada($baru->id))->toBe(KrsPersetujuan::BELUM);
});
