<?php

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Services\AcademicRuleService;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

/** Semester lalu yang sudah ditempuh dengan nilai tertentu pada MK 3 SKS. */
function semesterLalu($test, array $d, string $tahun, string $smt, string $nilai, ?MataKuliah $mk = null): TahunAkademik
{
    $ta = TahunAkademik::create(['tahun' => $tahun, 'semester' => $smt, 'is_active' => false,
        'tanggal_mulai_krs' => now()->subYear()->toDateString(), 'tanggal_selesai_krs' => now()->subYear()->toDateString()]);
    $d['mhs']->mataKuliahs()->attach(($mk ?? $d['mk'])->id, ['tahun_akademik_id' => $ta->id, 'nilai' => $nilai]);
    $d['mhs']->setStatusKrs($ta->id, 'Disetujui', null, $d['dUser']->id);
    return $ta;
}

test('batas SKS: semester 1 = 20; semester 2 dst. mengikuti IPK hingga 24', function () {
    $d = $this->seedAkademik();
    $svc = app(AcademicRuleService::class);
    $batas = fn () => $svc->hitungBatasSks(\App\Models\Mahasiswa::find($d['mhs']->id));

    expect($batas())->toMatchArray(['semester_ke' => 1, 'max_sks' => 20]);

    semesterLalu($this, $d, '2025/2026', 'Genap', 'A');
    expect($batas())->toMatchArray(['semester_ke' => 2, 'max_sks' => 24]);

    // IPK turun ke 2,50 (A + D) → 21 SKS di semester 3
    semesterLalu($this, $d, '2024/2025', 'Genap', 'D', $d['mkLain']);
    expect($batas())->toMatchArray(['semester_ke' => 3, 'max_sks' => 21]);
});

test('batas SKS: semester cuti/tanpa KRS tidak menambah hitungan semester, nilai belum keluar tetap 20', function () {
    $d = $this->seedAkademik();
    // semester lalu KRS ada tetapi belum bernilai & tidak disetujui (mis. ditolak lalu cuti)
    $ta = TahunAkademik::create(['tahun' => '2025/2026', 'semester' => 'Genap', 'is_active' => false,
        'tanggal_mulai_krs' => now()->subYear()->toDateString(), 'tanggal_selesai_krs' => now()->subYear()->toDateString()]);
    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $ta->id]);
    $d['mhs']->setStatusKrs($ta->id, 'Ditolak');

    $b = app(AcademicRuleService::class)->hitungBatasSks($d['mhs']->fresh());
    expect($b['semester_ke'])->toBe(1)->and($b['max_sks'])->toBe(20);

    // KRS semester lalu disetujui tetapi nilai belum diinput dosen → semester 2, batas tetap 20
    $d['mhs']->setStatusKrs($ta->id, 'Disetujui', null, $d['dUser']->id);
    $b = app(AcademicRuleService::class)->hitungBatasSks($d['mhs']->fresh());
    expect($b['semester_ke'])->toBe(2)->and($b['max_sks'])->toBe(20);
});

test('KRS menolak lebih dari 20 SKS untuk mahasiswa semester 1 di web', function () {
    $d = $this->seedAkademik();
    \App\Models\Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1, 'semester' => 'x', 'status' => 'lunas', 'jenis_pembayaran' => 'spp']);
    $besar = MataKuliah::create(['kurikulum_id' => $d['mk']->kurikulum_id, 'kode_mk' => 'TH199', 'nama_mk' => 'Besar', 'sks' => 18, 'semester' => 1, 'dosen_id' => $d['dosen']->id]);
    $besar->jadwals()->create(['nama_kelas' => 'A', 'hari' => 'Kamis', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00', 'kuota' => 40]);

    $this->actingAs($d['mUser'])->get(route('krs.index'))->assertOk()->assertSee('/ 20 SKS', false)->assertSee('SEMESTER KE-1');
    $this->actingAs($d['mUser'])->post(route('krs.store'), ['mata_kuliahs' => [$d['mk']->id, $besar->id], 'nama_kelas' => [$d['mk']->id => 'A', $besar->id => 'A']])
        ->assertSessionHasErrors('mata_kuliahs');
});

test('BKD pengajaran: per kelas paralel dan team teaching SKS penuh untuk setiap dosen', function () {
    $d = $this->seedAkademik(); // Dogmatika (3 SKS) rombel A & B, diampu Dosen A
    $tUser = $this->buatUser('Dosen Tim', 'tim@x.test', ['dosen']);
    $tim = Dosen::create(['user_id' => $tUser->id, 'nidn' => '888', 'nama_lengkap' => 'Dosen Tim', 'jenis_pengajar' => 'Dosen']);
    $d['mk']->teamDosens()->attach($tim->id, ['is_utama' => false]);

    $bkdTim = $tim->fresh()->hitungRingkasanBkd();
    expect($bkdTim['sks_pengajaran'])->toBe(6.0)->and($bkdTim['jumlah_kelas_ajar'])->toBe(2);

    // MK semester genap tidak masuk BKD semester ganjil
    MataKuliah::create(['kurikulum_id' => $d['mk']->kurikulum_id, 'kode_mk' => 'TH202', 'nama_mk' => 'Etika', 'sks' => 3, 'semester' => 2, 'dosen_id' => $tim->id]);
    expect($tim->fresh()->hitungRingkasanBkd()['sks_pengajaran'])->toBe(6.0);

    // dashboard dosen & laporan mutu memakai angka yang sama
    $this->actingAs($tUser)->get(route('dosen.dashboard'))->assertOk()->assertSee('PENGAJARAN: 6');
    $mutu = $this->buatUser('LPM', 'lpm@x.test', ['penjaminan_mutu']);
    $this->actingAs($mutu)->get(route('mutu.laporan.cetak-beban-dosen'))->assertOk();
});
