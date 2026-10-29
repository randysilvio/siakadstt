<?php

use App\Models\Dosen;
use App\Models\EvaluasiSesi;
use App\Models\KegiatanAkademik;
use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use App\Models\PmbPeriod;
use App\Models\Role;
use App\Models\User;
use App\Services\KelengkapanDataService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

function acaraKalender($t, array $d, array $isi)
{
    return $t->actingAs($d['admin'])->post(route('admin.kalender.store'), array_merge([
        'judul_kegiatan' => 'Acara', 'tanggal_mulai' => now()->toDateString(), 'tanggal_selesai' => now()->toDateString(),
        'target_roles' => [Role::where('name', 'mahasiswa')->value('id')],
    ], $isi));
}

test('periode KRS dari kalender mengisi Tahun Akademik, tanggal selesai ikut dihitung, saklar manual tetap menang', function () {
    $d = $this->seedAkademik();
    Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1, 'semester' => 'x', 'status' => 'lunas', 'jenis_pembayaran' => 'spp']);

    // Periode sudah lewat → KRS tertutup
    acaraKalender($this, $d, ['judul_kegiatan' => 'KRS Ganjil', 'jenis' => 'krs', 'tahun_akademik_id' => $d['ta']->id,
        'tanggal_mulai' => now()->subDays(10)->toDateString(), 'tanggal_selesai' => now()->subDay()->toDateString()])->assertRedirect();
    expect($d['ta']->fresh()->tanggal_selesai_krs)->toStartWith(now()->subDay()->toDateString());
    $this->actingAs($d['mUser'], 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'A']])->assertForbidden();

    // Perpanjang sampai HARI INI → masih bisa (inklusif 23.59)
    $k = KegiatanAkademik::where('jenis', 'krs')->first();
    $this->actingAs($d['admin'])->put(route('admin.kalender.update', $k), [
        'judul_kegiatan' => 'KRS Ganjil', 'jenis' => 'krs', 'tahun_akademik_id' => $d['ta']->id,
        'tanggal_mulai' => now()->subDays(10)->toDateString(), 'tanggal_selesai' => now()->toDateString(), 'target_roles' => $k->roles->pluck('id')->all(),
    ])->assertRedirect();
    $this->actingAs($d['mUser'], 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'A']])->assertOk();

    // Saklar manual "tutup" menang atas kalender
    Pengaturan::set('status_krs', 'tutup');
    $this->actingAs($d['mUser'], 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'B']])->assertForbidden();

    // Tanggal di form Tahun Akademik terkunci: kiriman manual diabaikan
    $this->actingAs($d['admin'])->put(route('admin.tahun-akademik.update', $d['ta']), [
        'tahun' => $d['ta']->tahun, 'semester' => $d['ta']->semester, 'tanggal_mulai_krs' => '2020-01-01', 'tanggal_selesai_krs' => '2020-01-02',
    ])->assertRedirect();
    expect($d['ta']->fresh()->tanggal_selesai_krs)->toStartWith(now()->toDateString());

    // Satu periode KRS per Tahun Akademik
    acaraKalender($this, $d, ['jenis' => 'krs', 'tahun_akademik_id' => $d['ta']->id])->assertSessionHasErrors('jenis');
    acaraKalender($this, $d, ['jenis' => 'validasi_krs'])->assertSessionHasErrors('jenis'); // wajib pilih Tahun Akademik
});

test('periode validasi KRS membatasi Dosen Wali tetapi tidak Admin', function () {
    $d = $this->seedAkademik();
    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
    $d['mhs']->setStatusKrs($d['ta']->id, 'Menunggu Persetujuan');
    acaraKalender($this, $d, ['jenis' => 'validasi_krs', 'tahun_akademik_id' => $d['ta']->id,
        'tanggal_mulai' => now()->addDays(2)->toDateString(), 'tanggal_selesai' => now()->addDays(9)->toDateString()]);

    $r = $this->actingAs($d['dUser'], 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Setujui'])->assertStatus(422);
    expect($r->json('message'))->toContain('belum dibuka');
    $this->actingAs($d['admin'], 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Setujui'])->assertOk();
    expect($d['mhs']->fresh()->krsDisetujuiPada($d['ta']->id))->toBeTrue();
    expect(LogAktivitas::where('subjek_tipe', 'KrsPersetujuan')->where('keterangan', 'like', '%Disetujui%')->exists())->toBeTrue();
});

test('periode input nilai mengunci penyimpanan nilai dosen setelah lewat; admin tetap bisa', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    acaraKalender($this, $d, ['jenis' => 'input_nilai', 'tahun_akademik_id' => $d['ta']->id,
        'tanggal_mulai' => now()->subDays(20)->toDateString(), 'tanggal_selesai' => now()->subDay()->toDateString()]);

    $isi = ['mata_kuliah_id' => $d['mk']->id, 'peserta' => [$d['mhs']->id], 'nilai_kehadiran' => [$d['mhs']->id => 90], 'nilai_tugas' => [$d['mhs']->id => 90],
        'nilai_uts' => [$d['mhs']->id => 90], 'nilai_uas' => [$d['mhs']->id => 90]];
    $this->actingAs($d['dUser'])->post(route('nilai.store'), $isi)->assertSessionHas('error');
    expect($d['mhs']->mataKuliahs()->first()->pivot->nilai)->toBeNull();
    $this->actingAs($d['dUser'])->get(route('nilai.show', $d['mk']))->assertOk()->assertSee('sudah ditutup');

    $this->actingAs($d['admin'])->post(route('nilai.store'), $isi)->assertSessionHas('success');
    expect($d['mhs']->mataKuliahs()->first()->pivot->nilai)->toBe('A');
});

test('periode EDOM membuat sesi otomatis (hari terakhir ikut dihitung) dan gelombang PMB mengikuti kalender', function () {
    $d = $this->seedAkademik();
    acaraKalender($this, $d, ['judul_kegiatan' => 'EDOM Ganjil', 'jenis' => 'edom', 'tahun_akademik_id' => $d['ta']->id,
        'tanggal_mulai' => now()->subDays(5)->toDateString(), 'tanggal_selesai' => now()->toDateString()]);
    $sesi = EvaluasiSesi::first();
    expect($sesi)->not->toBeNull()->and((bool) $sesi->is_active)->toBeTrue()->and($sesi->nama_sesi)->toBe('EDOM Ganjil');
    expect(EvaluasiSesi::berjalan()->count())->toBe(1); // hari terakhir masih berjalan

    $k = KegiatanAkademik::where('jenis', 'edom')->first();
    $this->actingAs($d['admin'])->delete(route('admin.kalender.destroy', $k))->assertRedirect();
    expect(EvaluasiSesi::count())->toBe(0); // belum ada jawaban → sesi buatan kalender dihapus

    $p = PmbPeriod::create(['nama_gelombang' => 'Gelombang 1', 'tahun_pmb' => 2027, 'tanggal_buka' => '2027-01-01', 'tanggal_tutup' => '2027-02-01', 'biaya_pendaftaran' => 0, 'is_active' => true, 'jenis_ujian' => 'offline']);
    acaraKalender($this, $d, ['jenis' => 'pmb', 'pmb_period_id' => $p->id, 'tanggal_mulai' => '2027-03-01', 'tanggal_selesai' => '2027-04-15']);
    expect($p->fresh()->tanggal_tutup->toDateString())->toBe('2027-04-15');
});

test('log aktivitas mencatat login, perubahan data (lama → baru), dan perubahan peran', function () {
    $d = $this->seedAkademik();
    $this->post('/login', ['email' => 'admin@x.test', 'password' => 'password123']);
    expect($d['admin']->fresh()->last_login_at)->not->toBeNull();
    expect(LogAktivitas::where('aksi', 'login')->where('user_id', $d['admin']->id)->exists())->toBeTrue();

    $d['mhs']->update(['nomor_telepon' => '0811']);
    $log = LogAktivitas::where('subjek_tipe', 'Mahasiswa')->where('aksi', 'ubah')->latest('id')->first();
    expect($log->user_id)->toBe($d['admin']->id)->and($log->perubahan['nomor_telepon'][1])->toBe('0811');

    $this->actingAs($d['admin'])->put(route('admin.user.update', $d['dUser']), [
        'name' => $d['dUser']->name, 'email' => $d['dUser']->email, 'roles' => [Role::where('name', 'dosen')->value('id'), Role::firstOrCreate(['name' => 'penjaminan_mutu'], ['display_name' => 'PM'])->id],
    ]);
    expect(LogAktivitas::where('aksi', 'peran')->exists())->toBeTrue();

    $d['dUser']->forceFill(['last_login_at' => now()->subHours(3)])->saveQuietly();
    $this->actingAs($d['admin'])->get(route('dashboard'))->assertOk()->assertSee('Log Aktivitas Penting')->assertSee('Pemantauan Login Terakhir')
        ->assertSee($d['dUser']->name)->assertSee('3 jam');
});

test('pengingat kelengkapan data: tampil untuk admin, menyebut bidang, dan notifikasi mingguan ke bidang terkait', function () {
    Notification::fake();
    $d = $this->seedAkademik();
    $kUser = $this->buatUser('Kaprodi', 'kap@x.test', ['dosen']);
    $kaprodi = Dosen::create(['user_id' => $kUser->id, 'nidn' => '9', 'nama_lengkap' => 'Kaprodi', 'jenis_pengajar' => 'Dosen', 'program_studi_id' => $d['prodi']->id]);
    $d['prodi']->update(['kaprodi_dosen_id' => $kaprodi->id]);
    $keu = $this->buatUser('Keuangan', 'keu@x.test', ['keuangan']);
    $d['mhs']->update(['dosen_wali_id' => null, 'nik' => '123']);

    $r = app(KelengkapanDataService::class)->ringkasan(true);
    $kunci = collect($r['butir'])->pluck('kunci');
    expect($kunci)->toContain('nik_tidak_valid')->toContain('tanpa_tagihan')->toContain('tanpa_wali_' . $d['prodi']->id)->toContain('edom_belum_ada');
    expect(collect($r['butir'])->firstWhere('kunci', 'tanpa_tagihan')['bidang'])->toBe('Keuangan');

    $this->actingAs($d['admin'])->get(route('dashboard'))->assertOk()->assertSee('pengingat-admin-btn', false)->assertSee('SALIN PESAN UNTUK BIDANG INI');
    $this->actingAs($d['dUser'])->get(route('dashboard'))->assertOk()->assertDontSee('pengingat-admin-btn', false);
    $this->actingAs($d['admin'])->post(route('admin.kelengkapan.segarkan'))->assertSessionHas('success');

    $this->artisan('siakad:pengingat-kelengkapan')->assertSuccessful();
    Notification::assertSentTo($keu, \App\Notifications\GeneralNotification::class);
    Notification::assertSentTo($kUser, \App\Notifications\GeneralNotification::class);
    Notification::assertNotSentTo($d['admin'], \App\Notifications\GeneralNotification::class);
});

test('pengingat kalender H-1 dikirim sekali ke peran sasaran', function () {
    $d = $this->seedAkademik();
    Notification::fake();
    acaraKalender($this, $d, ['judul_kegiatan' => 'UAS', 'tanggal_mulai' => now()->addDay()->toDateString(), 'tanggal_selesai' => now()->addDays(5)->toDateString()]);
    Notification::fake(); // abaikan notifikasi "agenda baru"

    $this->artisan('siakad:pengingat-kalender')->assertSuccessful();
    $this->artisan('siakad:pengingat-kalender')->assertSuccessful();
    Notification::assertSentToTimes($d['mUser'], \App\Notifications\GeneralNotification::class, 1);
});

test('paginasi seragam berbahasa Indonesia dan mempertahankan filter pencarian', function () {
    $this->get('/?q=dogmatika&prodi=2'); // set request query
    request()->query->add(['q' => 'dogmatika', 'prodi' => '2']);
    $p = new LengthAwarePaginator(range(1, 15), 120, 15, 3, ['path' => url('/admin/mahasiswa')]);
    $html = (string) $p->links();
    expect($html)->toContain('Menampilkan 31–45 dari 120 data')->toContain('q=dogmatika')->toContain('prodi=2')->toContain('page=4')->toContain('&hellip;');
    expect((string) $p->links('pagination::siakad-publik'))->toContain('Menampilkan 31–45 dari 120');
});

test('pencarian di daftar Kalender Akademik benar-benar menyaring agenda', function () {
    $d = $this->seedAkademik();
    KegiatanAkademik::create(['judul_kegiatan' => 'Wisuda Angkatan 2022', 'tanggal_mulai' => now(), 'tanggal_selesai' => now()]);
    KegiatanAkademik::create(['judul_kegiatan' => 'Ujian Akhir Semester', 'tanggal_mulai' => now(), 'tanggal_selesai' => now()]);
    $this->actingAs($d['admin'])->get(route('admin.kalender.index', ['search' => 'wisuda']))->assertOk()
        ->assertSee('Wisuda Angkatan 2022')->assertDontSee('Ujian Akhir Semester');
});
