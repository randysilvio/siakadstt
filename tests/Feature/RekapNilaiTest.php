<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Services\RekapNilaiService;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

test('IPK memakai nilai terbaik mata kuliah ulang dan SKS lulus tanpa E', function () {
    $d = $this->seedAkademik();
    $taLama = TahunAkademik::create(['tahun' => '2025/2026', 'semester' => 'Genap', 'is_active' => false,
        'tanggal_mulai_krs' => now()->toDateString(), 'tanggal_selesai_krs' => now()->toDateString()]);

    // Dogmatika (3 SKS): E lalu diulang B → pakai B. Didaktik (3 SKS): E.
    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $taLama->id, 'nilai' => 'E']);
    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nilai' => 'B']);
    $d['mhs']->mataKuliahs()->attach($d['mkLain']->id, ['tahun_akademik_id' => $d['ta']->id, 'nilai' => 'E']);

    $rekap = app(RekapNilaiService::class)->rekap($d['mhs']->id);

    expect($rekap['ipk'])->toBe(1.5)              // (3×3 + 0×3) / 6
        ->and($rekap['sks_lulus'])->toBe(3)
        ->and($rekap['sks_bernilai'])->toBe(6)
        ->and(Mahasiswa::find($d['mhs']->id)->hitungIpk())->toBe(1.5);
});

test('mahasiswa tanpa nilai ber-IPK 0 dan mendapat batas 24 SKS', function () {
    $d = $this->seedAkademik();
    $batas = app(\App\Services\AcademicRuleService::class)->hitungBatasSks($d['mhs']);
    expect($batas['ipk'])->toBe(0.0)->and($batas['max_sks'])->toBe(24);
});

test('dashboard Kaprodi tidak menambah query per mahasiswa', function () {
    $d = $this->seedAkademik();
    $kUser = $this->buatUser('Kaprodi', 'k@x.test', ['dosen']);
    $kaprodi = Dosen::create(['user_id' => $kUser->id, 'nidn' => '333', 'nama_lengkap' => 'Kaprodi', 'jenis_pengajar' => 'Dosen']);
    $d['prodi']->update(['kaprodi_dosen_id' => $kaprodi->id]);

    $hitungQuery = function () use ($kUser) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($kUser)->get(route('kaprodi.dashboard'))->assertOk();
        $n = count(DB::getQueryLog());
        DB::disableQueryLog();
        return $n;
    };

    $tambahMahasiswa = function (int $mulai, int $jumlah) use ($d) {
        for ($i = $mulai; $i < $mulai + $jumlah; $i++) {
            $m = Mahasiswa::create(['nim' => "2026TH1{$i}", 'nama_lengkap' => "Mhs {$i}", 'program_studi_id' => $d['prodi']->id,
                'status_mahasiswa' => 'Aktif', 'tahun_masuk' => 2026, 'jenis_kelamin' => 'L', 'nama_ibu_kandung' => 'Ibu']);
            $m->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nilai' => $i % 2 ? 'E' : 'D']);
        }
    };

    $tambahMahasiswa(0, 3);
    $hitungQuery(); // pemanasan: cache pengaturan, peran, dsb.
    $sedikit = $hitungQuery();

    $tambahMahasiswa(10, 6);
    $banyak = $hitungQuery();

    // Penambahan 6 mahasiswa tidak boleh menambah query (dulu ±6 query per mahasiswa)
    expect($banyak)->toBe($sedikit);
});
