<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\TracerStudy;
use App\Services\IndikatorAkreditasiService as S;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

test('rumus skor mengikuti matriks BAN-PT / LAMDIK rumpun Sosial Humaniora', function () {
    expect(S::skorRasio(30))->toBe(4.0)->and(S::skorRasio(20))->toBe(3.2)->and(S::skorRasio(40))->toBe(2.67)->and(S::skorRasio(60))->toBe(0.0)
        ->and(S::skorJumlahDtps(12))->toBe(4.0)->and(S::skorJumlahDtps(6))->toBe(2.67)->and(S::skorJumlahDtps(2))->toBe(0.0)
        ->and(S::skorDosenTidakTetap(10))->toBe(4.0)->and(S::skorDosenTidakTetap(25))->toBe(3.0)->and(S::skorDosenTidakTetap(50))->toBe(0.0)
        ->and(S::skorBimbingan(5))->toBe(4.0)->and(S::skorBimbingan(8))->toBe(3.0)
        ->and(S::skorIpkLulusan(3.3))->toBe(4.0)->and(S::skorIpkLulusan(3.0))->toBe(3.6)
        ->and(S::skorTepatWaktu(60))->toBe(4.0)->and(S::skorTepatWaktu(25))->toBe(2.5)
        ->and(S::skorWaktuTunggu(3))->toBe(4.0)->and(S::skorWaktuTunggu(9))->toBe(3.0)
        ->and(S::skorKesesuaian(75))->toBe(4.0)->and(S::skorKesesuaian(30))->toBe(2.0)
        ->and(S::respondenMinimal(300))->toBe(30.0)->and(S::respondenMinimal(150))->toBe(40.0);
});

test('rasio per prodi memakai dosen tetap homebase; dosen tidak tetap dihitung terpisah', function () {
    $d = $this->seedAkademik();
    $d['dosen']->update(['status_kepegawaian' => 'Dosen Tetap']);
    // dosen tidak tetap yang mengampu MK prodi TH
    $lbUser = $this->buatUser('Dosen LB', 'lb@x.test', ['dosen']);
    $lb = Dosen::create(['user_id' => $lbUser->id, 'nidn' => '901', 'nama_lengkap' => 'Dosen LB', 'jenis_pengajar' => 'Dosen Tamu', 'status_kepegawaian' => 'Dosen Tidak Tetap']);
    $d['mk']->teamDosens()->attach($lb->id, ['is_utama' => false]);
    for ($i = 2; $i <= 30; $i++) {
        Mahasiswa::create(['nim' => "2026TH0{$i}", 'nama_lengkap' => "M{$i}", 'program_studi_id' => $d['prodi']->id, 'status_mahasiswa' => 'Aktif',
            'tahun_masuk' => 2026, 'jenis_kelamin' => 'L', 'nama_ibu_kandung' => 'Ibu']);
    }

    $th = app(S::class)->perProdi()->firstWhere('prodi.id', $d['prodi']->id);
    expect($th->mahasiswa)->toBe(30)->and($th->dtps)->toBe(1)->and($th->dtt)->toBe(1)
        ->and($th->rasio_teks)->toBe('1 : 30')->and($th->skor_rasio)->toBe(4.0)->and($th->persen_dtt)->toBe(50.0);

    $lpm = $this->buatUser('LPM', 'lpm@x.test', ['penjaminan_mutu']);
    $this->actingAs($lpm)->get(route('mutu.dashboard'))->assertOk()
        ->assertSee('Indikator Kuantitatif Akreditasi')->assertSee('1 : 30')->assertViewHas('jumlahDosen', 1);
    $rektor = $this->buatUser('Rektor', 'rek@x.test', ['rektorat']);
    $this->actingAs($rektor)->get(route('rektorat.dashboard'))->assertOk()->assertSee('1 : 30');
    $this->actingAs($lpm)->get(route('mutu.laporan.cetak-ringkasan'))->assertOk();
});

test('indikator lulusan & tracer 3 tahun terakhir', function () {
    $d = $this->seedAkademik();
    $l = Mahasiswa::create(['nim' => '2021TH001', 'nama_lengkap' => 'Alumni', 'program_studi_id' => $d['prodi']->id, 'status_mahasiswa' => 'Lulus',
        'tahun_masuk' => now()->year - 4, 'tanggal_lulus' => now()->setMonth(8)->setDay(15)->subYear()->toDateString(), 'jenis_kelamin' => 'L', 'nama_ibu_kandung' => 'Ibu']);
    $l->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nilai' => 'A']);
    TracerStudy::create(['mahasiswa_id' => $l->id, 'tahun_lulus' => now()->year - 1, 'status_pekerjaan' => 'Bekerja', 'waktu_tunggu_bulan' => 3, 'kesesuaian_bidang' => 'Sesuai']);

    $svc = app(S::class);
    $lulusan = $svc->lulusan();
    expect($lulusan['jumlah'])->toBe(1)->and($lulusan['rata_ipk'])->toBe(4.0)->and($lulusan['skor_ipk'])->toBe(4.0)
        ->and($lulusan['tepat_waktu'])->toBe(100.0);

    $tracer = $svc->tracer();
    expect($tracer['persen_responden'])->toBe(100.0)->and($tracer['skor_wt'])->toBe(4.0)->and($tracer['skor_pbs'])->toBe(4.0);
});

test('SK kepanitiaan hanya dihitung bila terbit di semester aktif', function () {
    $d = $this->seedAkademik(); // TA aktif 2026/2027 Ganjil → Agustus 2026 – Januari 2027
    $baru = \App\Models\SuratKeputusan::create(['jenis_surat' => 'SK', 'judul' => 'Panitia Natal', 'status' => 'Selesai', 'tanggal_terbit' => '2026-11-01']);
    $lama = \App\Models\SuratKeputusan::create(['jenis_surat' => 'SK', 'judul' => 'Panitia Lama', 'status' => 'Selesai', 'tanggal_terbit' => '2025-03-01']);
    $baru->dosens()->attach($d['dosen']->id, ['jabatan_dalam_surat' => 'Ketua']);
    $lama->dosens()->attach($d['dosen']->id, ['jabatan_dalam_surat' => 'Ketua']);

    expect($d['dosen']->fresh()->hitungRingkasanBkd()['sks_penunjang'])->toBe(0.5);
});
