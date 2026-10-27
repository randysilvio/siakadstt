<?php

use App\Models\Camaba;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\PmbKepanitiaan;
use App\Models\PmbPeriod;
use App\Models\User;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

/** Membuat gelombang + satu pendaftar siap seleksi pada tahun PMB tertentu. */
function pendaftarPmb($test, int $tahun, string $email, array $d, string $status = 'menunggu_verifikasi', string $bayar = 'lunas'): Camaba
{
    $periode = PmbPeriod::firstOrCreate(['tahun_pmb' => $tahun], [
        'nama_gelombang' => "Gelombang I {$tahun}", 'tanggal_buka' => now()->subMonth(), 'tanggal_tutup' => now()->addMonth(),
        'biaya_pendaftaran' => 250000, 'is_active' => true, 'jenis_ujian' => 'offline',
    ]);
    $u = $test->buatUser('Calon ' . $email, $email, ['camaba']);
    Pembayaran::create(['user_id' => $u->id, 'jenis_pembayaran' => 'formulir_pmb', 'jumlah' => 250000, 'status' => $bayar, 'semester' => 'PMB']);

    return Camaba::create([
        'user_id' => $u->id, 'pmb_period_id' => $periode->id, 'no_pendaftaran' => 'REG-' . $u->id,
        'pilihan_prodi_1_id' => $d['prodi']->id, 'tempat_lahir' => 'Fakfak', 'tanggal_lahir' => '2007-01-01',
        'jenis_kelamin' => 'L', 'status_pendaftaran' => $status,
    ]);
}

function tunjukPanitia(User $user, int $tahun, string $jabatan = 'Anggota'): PmbKepanitiaan
{
    return PmbKepanitiaan::create(['tahun_pmb' => $tahun, 'user_id' => $user->id, 'jabatan' => $jabatan]);
}

test('admin menunjuk panitia dari akun yang ada dan akun khusus baru', function () {
    $d = $this->seedAkademik();
    $tendik = $this->buatUser('Staf BAAK', 'baak@x.test', ['administrasi_umum']);

    $this->actingAs($d['admin'])->post(route('admin.pmb-panitia.store'), [
        'tahun_pmb' => 2027, 'jabatan' => 'Ketua', 'mode' => 'akun_lama', 'user_id' => $tendik->id,
    ])->assertSessionHas('success');

    $this->actingAs($d['admin'])->post(route('admin.pmb-panitia.store'), [
        'tahun_pmb' => 2027, 'jabatan' => 'Anggota', 'mode' => 'akun_baru',
        'name' => 'Panitia Tamu', 'email' => 'tamu@x.test', 'password' => 'RahasiaPmb2027', 'password_confirmation' => 'RahasiaPmb2027',
    ])->assertSessionHas('success');

    expect($tendik->fresh()->isPanitiaPmb())->toBeTrue()
        ->and(User::where('email', 'tamu@x.test')->first()->isPanitiaPmb())->toBeTrue()
        ->and($tendik->notifications()->count())->toBe(1);

    $this->actingAs($d['admin'])->get(route('admin.pmb-panitia.index', ['tahun' => 2027]))->assertOk()->assertSee('Staf BAAK');
});

test('mahasiswa tidak dapat ditunjuk sebagai panitia', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['admin'])->post(route('admin.pmb-panitia.store'), [
        'tahun_pmb' => 2027, 'jabatan' => 'Anggota', 'mode' => 'akun_lama', 'user_id' => $d['mUser']->id,
    ])->assertSessionHas('error');
    expect(PmbKepanitiaan::count())->toBe(0);
});

test('panitia hanya melihat dan memproses pendaftar tahun kepanitiaannya', function () {
    $d = $this->seedAkademik();
    $panitia = $this->buatUser('Panitia', 'panitia@x.test', ['administrasi_umum']);
    tunjukPanitia($panitia, 2027);

    $milik = pendaftarPmb($this, 2027, 'a@x.test', $d);
    $lain = pendaftarPmb($this, 2026, 'b@x.test', $d);

    $this->actingAs($panitia)->get(route('admin.pmb.index'))->assertOk()
        ->assertSee($milik->no_pendaftaran)->assertDontSee($lain->no_pendaftaran);

    $this->actingAs($panitia)->get(route('admin.pmb.show', $lain))->assertForbidden();
    $this->actingAs($panitia)->put(route('admin.pmb.approve', $lain))->assertForbidden();

    $this->actingAs($panitia)->put(route('admin.pmb.approve', $milik))->assertRedirect(route('admin.pmb.index'));

    $mhs = Mahasiswa::where('user_id', $milik->user_id)->first();
    expect($mhs)->not->toBeNull()
        ->and((string) $mhs->tahun_masuk)->toBe('2027')                 // angkatan = tahun PMB, bukan tanggal hari ini
        ->and($mhs->nim)->toStartWith('2027TH')
        ->and((int) $milik->fresh()->diputuskan_oleh)->toBe($panitia->id);
});

test('panitia memverifikasi bayar formulir dan jejak verifikator tercatat', function () {
    $d = $this->seedAkademik();
    $panitia = $this->buatUser('Panitia', 'panitia@x.test', ['dosen']);
    tunjukPanitia($panitia, 2027, 'Bendahara');
    $camaba = pendaftarPmb($this, 2027, 'a@x.test', $d, 'draft', 'menunggu_konfirmasi');
    $tagihan = Pembayaran::where('user_id', $camaba->user_id)->first();

    $this->actingAs($panitia)->post(route('admin.pmb.payment.approve', $tagihan->id))->assertSessionHas('success');

    $tagihan->refresh();
    expect($tagihan->status)->toBe('lunas')->and((int) $tagihan->diverifikasi_oleh)->toBe($panitia->id);

    $this->actingAs($panitia)->get(route('admin.pmb.show', $camaba))->assertOk()->assertSee('BAYAR DIVERIFIKASI');
});

test('alasan penolakan disimpan dan dikirim ke pendaftar', function () {
    $d = $this->seedAkademik();
    $panitia = $this->buatUser('Panitia', 'panitia@x.test', ['administrasi_umum']);
    tunjukPanitia($panitia, 2027);
    $camaba = pendaftarPmb($this, 2027, 'a@x.test', $d);

    $this->actingAs($panitia)->put(route('admin.pmb.reject', $camaba), ['alasan' => 'Ijazah tidak terbaca'])->assertSessionHas('success');

    $camaba->refresh();
    expect($camaba->status_pendaftaran)->toBe('tidak_lulus')
        ->and($camaba->catatan_seleksi)->toBe('Ijazah tidak terbaca')
        ->and($camaba->user->notifications()->first()->data['message'])->toContain('Ijazah tidak terbaca');
});

test('panitia tidak dapat menghapus pendaftar, mengatur gelombang, atau menunjuk panitia', function () {
    $d = $this->seedAkademik();
    $panitia = $this->buatUser('Panitia', 'panitia@x.test', ['administrasi_umum']);
    tunjukPanitia($panitia, 2027);
    $camaba = pendaftarPmb($this, 2027, 'a@x.test', $d);

    $this->actingAs($panitia)->delete(route('admin.pmb.destroy', $camaba->id))->assertRedirect(route('dashboard'));
    $this->actingAs($panitia)->get(route('admin.pmb-periods.index'))->assertRedirect(route('dashboard'));
    $this->actingAs($panitia)->get(route('admin.pmb-panitia.index'))->assertRedirect(route('dashboard'));
    expect(Camaba::find($camaba->id))->not->toBeNull();
});

test('menutup kepanitiaan mengakhiri akses panitia', function () {
    $d = $this->seedAkademik();
    $panitia = $this->buatUser('Panitia', 'panitia@x.test', ['administrasi_umum']);
    tunjukPanitia($panitia, 2027);

    $this->actingAs($panitia)->get(route('admin.pmb.index'))->assertOk();

    $this->actingAs($d['admin'])->post(route('admin.pmb-panitia.tutup'), ['tahun_pmb' => 2027])->assertSessionHas('success');

    $this->actingAs($panitia->fresh())->get(route('admin.pmb.index'))->assertRedirect(route('dashboard'));
    expect($panitia->fresh()->isPanitiaPmb())->toBeFalse();
});

test('akun khusus panitia diarahkan ke modul PMB dan melihat menu panitia', function () {
    $this->seedAkademik();
    $tamu = User::create(['name' => 'Panitia Tamu', 'email' => 'tamu@x.test', 'password' => bcrypt('RahasiaPmb2027')]);
    tunjukPanitia($tamu, 2027);

    $this->actingAs($tamu)->get('/dashboard')->assertRedirect(route('admin.pmb.index'));
    $this->actingAs($tamu)->get(route('admin.pmb.index'))->assertOk()->assertSee('Panitia PMB');
});

test('panitia dapat mencetak kartu ujian pendaftar tahunnya', function () {
    $d = $this->seedAkademik();
    $panitia = $this->buatUser('Panitia', 'panitia@x.test', ['administrasi_umum']);
    tunjukPanitia($panitia, 2027);
    $milik = pendaftarPmb($this, 2027, 'a@x.test', $d);
    $lain = pendaftarPmb($this, 2026, 'b@x.test', $d);

    $this->actingAs($panitia)->get(route('pmb.kartu-ujian.cetak', ['camaba_id' => $milik->id]))->assertOk();
    $this->actingAs($panitia)->get(route('pmb.kartu-ujian.cetak', ['camaba_id' => $lain->id]))->assertForbidden();
});

test('pendaftar baru memberi notifikasi ke panitia tahun gelombangnya', function () {
    $d = $this->seedAkademik();
    $panitia = $this->buatUser('Panitia', 'panitia@x.test', ['administrasi_umum']);
    tunjukPanitia($panitia, 2027);
    $periode = PmbPeriod::create(['nama_gelombang' => 'G1', 'tahun_pmb' => 2027, 'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addMonth(),
        'biaya_pendaftaran' => 250000, 'is_active' => true, 'jenis_ujian' => 'offline']);

    $this->post(route('pmb.register.store'), [
        'name' => 'Calon Baru', 'email' => 'calon@x.test', 'no_hp' => '0812', 'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123',
        'pmb_period_id' => $periode->id, 'pilihan_prodi_1_id' => $d['prodi']->id,
    ])->assertRedirect(route('dashboard'));

    expect($panitia->notifications()->count())->toBe(1)
        ->and($d['admin']->notifications()->count())->toBe(1);
});
