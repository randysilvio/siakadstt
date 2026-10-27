<?php

use App\Models\Dosen;
use App\Models\EvaluasiPertanyaan;
use App\Models\EvaluasiSesi;
use App\Models\KrsPersetujuan;
use App\Models\Mahasiswa;
use App\Models\Pengumuman;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\TahunAkademik;
use App\Models\VerumKelas;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

// T1 — KRS hanya menerima MK kurikulum aktif prodi sendiri
test('KRS menolak mata kuliah prodi lain', function () {
    $d = $this->seedAkademik();

    $this->actingAs($d['mUser'])->post('/krs', ['mata_kuliahs' => [$d['mkLain']->id]])
        ->assertSessionHasErrors('mata_kuliahs');

    expect($d['mhs']->mataKuliahs()->count())->toBe(0);
});

test('KRS prodi sendiri tersimpan dan menunggu persetujuan', function () {
    $d = $this->seedAkademik();

    $this->actingAs($d['mUser'])->post('/krs', ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'B']])
        ->assertSessionHasNoErrors();

    expect($d['mhs']->mataKuliahs()->first()->pivot->nama_kelas)->toBe('B')
        ->and($d['mhs']->fresh()->status_krs)->toBe(KrsPersetujuan::MENUNGGU)
        ->and($d['mhs']->statusKrsPada($d['ta']->id))->toBe(KrsPersetujuan::MENUNGGU);
});

test('KRS menolak rombel yang tidak ada di jadwal', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['mUser'])->post('/krs', ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'Z']])
        ->assertSessionHasErrors('mata_kuliahs');
});

// T3 — status KRS per semester & EDOM semester lalu
test('EDOM semester lalu tetap wajib setelah semester baru aktif', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);

    $taBaru = TahunAkademik::create(['tahun' => '2026/2027', 'semester' => 'Genap', 'is_active' => false,
        'tanggal_mulai_krs' => now()->toDateString(), 'tanggal_selesai_krs' => now()->addMonth()->toDateString()]);
    $this->actingAs($d['admin'])->patch(route('admin.tahun-akademik.set-active', $taBaru));

    EvaluasiSesi::create(['nama_sesi' => 'EDOM Ganjil', 'tahun_akademik_id' => $d['ta']->id,
        'tanggal_mulai' => now()->subDay(), 'tanggal_selesai' => now()->addWeek(), 'is_active' => true]);
    EvaluasiPertanyaan::create(['pertanyaan' => 'Dosen hadir tepat waktu', 'tipe_jawaban' => 'skala_1_5', 'urutan' => 1, 'is_active' => true]);

    $mhs = $d['mhs']->fresh();
    expect($mhs->status_krs)->toBe(KrsPersetujuan::BELUM)            // salinan semester aktif (baru)
        ->and($mhs->krsDisetujuiPada($d['ta']->id))->toBeTrue()      // riwayat semester lalu tetap
        ->and($mhs->status_evaluasi)->toBe('belum_isi');

    $this->actingAs($d['mUser'])->get('/evaluasi')->assertOk()->assertSee('Dogmatika');
});

// T4 — penetapan dosen wali hanya oleh Kaprodi/Admin
test('dosen biasa tidak dapat mengklaim mahasiswa bimbingan', function () {
    $d = $this->seedAkademik();
    $d['mhs']->update(['dosen_wali_id' => null]);

    $this->actingAs($d['dUser'])->post('/perwalian', ['mahasiswa_ids' => [$d['mhs']->id]])->assertSessionHas('error');
    expect($d['mhs']->fresh()->dosen_wali_id)->toBeNull();
});

test('kaprodi menetapkan dosen wali hanya untuk mahasiswa prodinya', function () {
    $d = $this->seedAkademik();
    $d['prodi']->update(['kaprodi_dosen_id' => $d['dosen']->id]);
    $u = $this->buatUser('Dosen B', 'b@x.test', ['dosen']);
    $dosenB = Dosen::create(['user_id' => $u->id, 'nidn' => '222', 'nama_lengkap' => 'Dosen B', 'jenis_pengajar' => 'Dosen', 'program_studi_id' => $d['prodi']->id]);

    $mhsLain = Mahasiswa::create(['nim' => '2026PK001', 'nama_lengkap' => 'Mhs PAK', 'program_studi_id' => $d['prodiLain']->id,
        'status_mahasiswa' => 'Aktif', 'tahun_masuk' => 2026, 'jenis_kelamin' => 'P', 'nama_ibu_kandung' => 'Ibu']);
    $d['mhs']->update(['dosen_wali_id' => null]);

    $this->actingAs($d['dUser'])->post('/perwalian', [
        'mahasiswa_ids' => [$d['mhs']->id, $mhsLain->id],
        'dosen_wali_id' => $dosenB->id,
    ])->assertSessionHas('success');

    expect((int) $d['mhs']->fresh()->dosen_wali_id)->toBe($dosenB->id)
        ->and($mhsLain->fresh()->dosen_wali_id)->toBeNull();
});

// T5 — validasi KRS: salah satu cukup, tidak saling membatalkan
test('kaprodi tidak dapat membatalkan KRS yang disetujui dosen wali', function () {
    $d = $this->seedAkademik();
    $kUser = $this->buatUser('Kaprodi', 'k@x.test', ['dosen']);
    $kaprodi = Dosen::create(['user_id' => $kUser->id, 'nidn' => '333', 'nama_lengkap' => 'Kaprodi', 'jenis_pengajar' => 'Dosen']);
    $d['prodi']->update(['kaprodi_dosen_id' => $kaprodi->id]);

    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
    $d['mhs']->setStatusKrs($d['ta']->id, KrsPersetujuan::MENUNGGU);

    $this->actingAs($d['dUser'])->patch(route('perwalian.updateStatus', $d['mhs']->id), ['status_krs' => 'Disetujui'])
        ->assertSessionHas('success');
    expect($d['mhs']->fresh()->status_krs)->toBe('Disetujui');

    $this->actingAs($kUser)->patch(route('kaprodi.krs.update', $d['mhs']), ['status_krs' => 'Ditolak'])
        ->assertSessionHas('error');
    expect($d['mhs']->fresh()->status_krs)->toBe('Disetujui');

    // Validator yang menyetujui dapat membukanya kembali
    $this->actingAs($d['dUser'])->patch(route('perwalian.updateStatus', $d['mhs']->id), ['status_krs' => 'Menunggu Persetujuan'])
        ->assertSessionHas('success');
    expect($d['mhs']->fresh()->status_krs)->toBe('Menunggu Persetujuan');
});

test('KRS kosong tidak dapat disetujui', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['dUser'])->patch(route('perwalian.updateStatus', $d['mhs']->id), ['status_krs' => 'Disetujui'])
        ->assertSessionHas('error');
});

// T6 — kaprodi di dua prodi
test('kaprodi dua prodi dapat memvalidasi mahasiswa prodi kedua', function () {
    $d = $this->seedAkademik();
    $kUser = $this->buatUser('Kaprodi', 'k@x.test', ['dosen']);
    $kaprodi = Dosen::create(['user_id' => $kUser->id, 'nidn' => '333', 'nama_lengkap' => 'Kaprodi', 'jenis_pengajar' => 'Dosen']);
    $d['prodi']->update(['kaprodi_dosen_id' => $kaprodi->id]);
    $d['prodiLain']->update(['kaprodi_dosen_id' => $kaprodi->id]);

    $mhsLain = Mahasiswa::create(['nim' => '2026PK001', 'nama_lengkap' => 'Mhs PAK', 'program_studi_id' => $d['prodiLain']->id,
        'status_mahasiswa' => 'Aktif', 'tahun_masuk' => 2026, 'jenis_kelamin' => 'P', 'nama_ibu_kandung' => 'Ibu']);
    $mhsLain->mataKuliahs()->attach($d['mkLain']->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
    $mhsLain->setStatusKrs($d['ta']->id, KrsPersetujuan::MENUNGGU);

    $this->actingAs($kUser)->get(route('kaprodi.krs.show', $mhsLain))->assertOk();
    $this->actingAs($kUser)->get(route('kaprodi.dashboard', ['prodi' => $d['prodiLain']->id]))->assertOk()->assertSee('Mhs PAK');
});

// T7 — EWS memuat IPK 0,00
test('mahasiswa dengan semua nilai E muncul di peringatan dini', function () {
    $d = $this->seedAkademik();
    $kUser = $this->buatUser('Kaprodi', 'k@x.test', ['dosen']);
    $kaprodi = Dosen::create(['user_id' => $kUser->id, 'nidn' => '333', 'nama_lengkap' => 'Kaprodi', 'jenis_pengajar' => 'Dosen']);
    $d['prodi']->update(['kaprodi_dosen_id' => $kaprodi->id]);
    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nilai' => 'E']);

    $response = $this->actingAs($kUser)->get(route('kaprodi.dashboard'));
    expect($response->viewData('mahasiswaRawanDo')->pluck('id')->all())->toContain($d['mhs']->id);
});

// T8 — Verum mengikuti rombel
test('mahasiswa rombel A tidak bisa masuk kelas Verum rombel B', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d, null, 'A');

    $kelasB = VerumKelas::create(['mata_kuliah_id' => $d['mk']->id, 'dosen_id' => $d['dosen']->id, 'tahun_akademik_id' => $d['ta']->id,
        'nama_kelas' => 'Dogmatika B', 'rombel' => 'B', 'kode_kelas' => 'KLSB01']);
    $kelasA = VerumKelas::create(['mata_kuliah_id' => $d['mk']->id, 'dosen_id' => $d['dosen']->id, 'tahun_akademik_id' => $d['ta']->id,
        'nama_kelas' => 'Dogmatika A', 'rombel' => 'A', 'kode_kelas' => 'KLSA01']);

    $this->actingAs($d['mUser'])->get(route('verum.show', $kelasB))->assertForbidden();
    $this->actingAs($d['mUser'])->get(route('verum.show', $kelasA))->assertOk();
});

test('sinkron nilai Verum dihitung per rombel', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d, null, 'A');

    $kelasA = VerumKelas::create(['mata_kuliah_id' => $d['mk']->id, 'dosen_id' => $d['dosen']->id, 'tahun_akademik_id' => $d['ta']->id,
        'nama_kelas' => 'A', 'rombel' => 'A', 'kode_kelas' => 'KLSA01']);
    $kelasB = VerumKelas::create(['mata_kuliah_id' => $d['mk']->id, 'dosen_id' => $d['dosen']->id, 'tahun_akademik_id' => $d['ta']->id,
        'nama_kelas' => 'B', 'rombel' => 'B', 'kode_kelas' => 'KLSB01']);

    $presA = $kelasA->presensi()->create(['judul_pertemuan' => 'P1', 'pertemuan_ke' => 1, 'waktu_buka' => now(), 'waktu_tutup' => now()->addHour()]);
    $kelasB->presensi()->create(['judul_pertemuan' => 'P1', 'pertemuan_ke' => 1, 'waktu_buka' => now(), 'waktu_tutup' => now()->addHour()]);
    DB::table('verum_kehadiran')->insert(['presensi_id' => $presA->id, 'mahasiswa_id' => $d['mhs']->id, 'status' => 'hadir', 'waktu_absen' => now(), 'created_at' => now(), 'updated_at' => now()]);

    $this->actingAs($d['dUser'])->post(route('nilai.syncVerum', $d['mk']));

    expect((float) DB::table('mahasiswa_mata_kuliah')->where('mahasiswa_id', $d['mhs']->id)->value('nilai_kehadiran'))->toBe(100.0);
});

// T9 / T10 — role
test('role mahasiswa yang dicabut tidak dikembalikan otomatis', function () {
    $d = $this->seedAkademik();
    $d['mUser']->roles()->detach();

    $this->actingAs($d['mUser'])->get('/dashboard');
    expect($d['mUser']->fresh()->hasRole('mahasiswa'))->toBeFalse();
});

test('update akun tanpa field peran tidak menghapus peran', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['admin'])->put(route('admin.user.update', $d['mUser']), ['name' => 'Mhs A Baru', 'email' => 'mhs@x.test']);
    expect($d['mUser']->fresh()->hasRole('mahasiswa'))->toBeTrue();
});

test('admin tidak dapat mencabut peran admin dirinya sendiri', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['admin'])->put(route('admin.user.update', $d['admin']), ['roles_submitted' => 1, 'roles' => []])
        ->assertSessionHas('error');
    expect($d['admin']->fresh()->hasRole('admin'))->toBeTrue();
});

// T12 — pengumuman internal
test('pengumuman bertarget mahasiswa dapat dibaca mahasiswa, tidak oleh dosen', function () {
    $d = $this->seedAkademik();
    $p = Pengumuman::create(['judul' => 'Rapat', 'slug' => 'rapat-x1', 'kategori' => 'pengumuman', 'konten' => '<p>Isi rapat</p>', 'target_role' => 'mahasiswa']);

    $this->actingAs($d['mUser'])->get(route('pengumuman.internal.show', $p))->assertOk()->assertSee('Isi rapat', false);
    $this->actingAs($d['dUser'])->get(route('pengumuman.internal.show', $p))->assertForbidden();
});

test('pengumuman tendik terkirim ke staf keuangan', function () {
    $d = $this->seedAkademik();
    $keu = $this->buatUser('Staf Keu', 'keu@x.test', ['keuangan']);
    $this->role('administrasi_umum');

    $this->actingAs($d['admin'])->post(route('admin.pengumuman.store'), [
        'judul' => 'Rapat Tendik', 'kategori' => 'pengumuman', 'konten' => '<p>isi</p>', 'target_role' => 'tendik',
    ]);

    expect($keu->notifications()->count())->toBe(1)
        ->and($d['mUser']->notifications()->count())->toBe(0);
});

test('admin dapat mengedit pengumuman (rute memakai slug)', function () {
    $d = $this->seedAkademik();
    $p = Pengumuman::create(['judul' => 'Lama', 'slug' => 'lama-x1', 'kategori' => 'berita', 'konten' => '<p>a</p>', 'target_role' => 'semua']);

    $this->actingAs($d['admin'])->put(route('admin.pengumuman.update', $p), [
        'judul' => 'Lama', 'kategori' => 'berita', 'konten' => '<p>b</p>', 'target_role' => 'semua',
    ])->assertRedirect(route('admin.pengumuman.index'));

    expect($p->fresh()->konten)->toBe('<p>b</p>');
});

// T13 — cetak KRS hanya setelah disetujui
test('KRS yang belum disetujui tidak dapat dicetak', function () {
    $d = $this->seedAkademik();
    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
    $d['mhs']->setStatusKrs($d['ta']->id, KrsPersetujuan::DITOLAK);

    $this->actingAs($d['mUser'])->get(route('krs.cetak.final'))->assertRedirect(route('krs.index'));
});

test('tahun akademik yang memiliki KRS tidak dapat dihapus', function () {
    $d = $this->seedAkademik();
    $taLama = TahunAkademik::create(['tahun' => '2025/2026', 'semester' => 'Genap', 'is_active' => false,
        'tanggal_mulai_krs' => now()->toDateString(), 'tanggal_selesai_krs' => now()->toDateString()]);
    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $taLama->id, 'nilai' => 'A']);

    $this->actingAs($d['admin'])->delete(route('admin.tahun-akademik.destroy', $taLama))->assertSessionHas('error');
    expect(TahunAkademik::find($taLama->id))->not->toBeNull();
});
