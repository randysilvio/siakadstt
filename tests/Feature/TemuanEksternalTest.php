<?php

use App\Models\AbsensiPegawai;
use App\Models\Dosen;
use App\Models\EvaluasiPertanyaan;
use App\Models\EvaluasiSesi;
use App\Models\LokasiKerja;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Tendik;
use App\Models\User;
use App\Models\VerumKelas;
use App\Models\VerumPengumpulan;
use App\Models\VerumTugas;
use Illuminate\Support\Str;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

test('EDOM: mata kuliah yang hanya diampu lewat team teaching tetap wajib dievaluasi', function () {
    $d = $this->seedAkademik();
    $mkTim = MataKuliah::create(['kurikulum_id' => $d['mk']->kurikulum_id, 'kode_mk' => 'TH120', 'nama_mk' => 'Homiletika', 'sks' => 2, 'semester' => 1, 'dosen_id' => null]);
    $mkTim->teamDosens()->attach($d['dosen']->id, ['is_utama' => false]);
    $d['mhs']->mataKuliahs()->attach($mkTim->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
    $d['mhs']->setStatusKrs($d['ta']->id, 'Disetujui', null, $d['dUser']->id);
    EvaluasiSesi::create(['nama_sesi' => 'EDOM', 'tahun_akademik_id' => $d['ta']->id, 'tanggal_mulai' => now()->subDay(), 'tanggal_selesai' => now()->addDay(), 'is_active' => true]);
    EvaluasiPertanyaan::create(['pertanyaan' => 'Tepat waktu', 'tipe_jawaban' => 'skala_1_5', 'is_active' => true, 'urutan' => 1]);

    expect(Mahasiswa::find($d['mhs']->id)->status_evaluasi)->toBe('belum_isi');
});

test('pengingat kuliah pagi hanya untuk rombel jadwal itu dan KRS yang disetujui', function () {
    $d = $this->seedAkademik(); // Dogmatika: A = Senin, B = Rabu
    $this->travelTo(now()->next('Monday')->setTime(6, 0));
    $this->ambilDanSetujui($d, $d['mk'], 'B'); // mahasiswa di kelas B (Rabu)

    $mA = Mahasiswa::create(['nim' => '2026TH002', 'nama_lengkap' => 'Mhs Kelas A', 'program_studi_id' => $d['prodi']->id, 'status_mahasiswa' => 'Aktif',
        'tahun_masuk' => 2026, 'jenis_kelamin' => 'L', 'nama_ibu_kandung' => 'Ibu', 'user_id' => $this->buatUser('Mhs Kelas A', 'a@x.test', ['mahasiswa'])->id]);
    $mA->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
    $mA->setStatusKrs($d['ta']->id, 'Disetujui', null, $d['dUser']->id);

    $mMenunggu = Mahasiswa::create(['nim' => '2026TH003', 'nama_lengkap' => 'Belum Disetujui', 'program_studi_id' => $d['prodi']->id, 'status_mahasiswa' => 'Aktif',
        'tahun_masuk' => 2026, 'jenis_kelamin' => 'L', 'nama_ibu_kandung' => 'Ibu', 'user_id' => $this->buatUser('Belum', 'c@x.test', ['mahasiswa'])->id]);
    $mMenunggu->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
    $mMenunggu->setStatusKrs($d['ta']->id, 'Menunggu Persetujuan');

    app(\App\Console\Kernel::class)->kirimPengingatKuliah();

    expect($mA->user->notifications()->count())->toBe(1)
        ->and($d['mUser']->notifications()->count())->toBe(0)
        ->and($mMenunggu->user->notifications()->count())->toBe(0);
});

test('tarik data Verum tidak menimpa nilai tugas dengan 0 bila tugas belum dinilai/dikumpulkan luring', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);
    $d['mhs']->mataKuliahs()->updateExistingPivot($d['mk']->id, ['nilai_tugas' => 85]);
    $kelas = VerumKelas::create(['mata_kuliah_id' => $d['mk']->id, 'dosen_id' => $d['dosen']->id, 'tahun_akademik_id' => $d['ta']->id,
        'nama_kelas' => 'Dogmatika', 'kode_kelas' => Str::random(6), 'rombel' => 'A']);
    $tugas = VerumTugas::create(['kelas_id' => $kelas->id, 'judul' => 'Esai', 'instruksi' => 'x', 'tenggat_waktu' => now()->addWeek()]);

    $this->actingAs($d['dUser'])->post(route('nilai.syncVerum', $d['mk']))->assertSessionHas('success');
    $pivot = fn () => $d['mhs']->mataKuliahs()->wherePivot('tahun_akademik_id', $d['ta']->id)->first()->pivot;
    expect((float) $pivot()->nilai_tugas)->toBe(85.0);

    // setelah tugas dinilai di Verum, nilai mengikuti Verum
    VerumPengumpulan::create(['tugas_id' => $tugas->id, 'mahasiswa_id' => $d['mhs']->id, 'file_path' => 'x.pdf', 'waktu_pengumpulan' => now(), 'nilai' => 70]);
    $this->actingAs($d['dUser'])->post(route('nilai.syncVerum', $d['mk']));
    expect((float) $pivot()->nilai_tugas)->toBe(70.0);
});

test('tautan notifikasi tidak dapat mengarahkan ke situs luar (//domain)', function () {
    $d = $this->seedAkademik();
    $d['mUser']->notify(new \App\Notifications\GeneralNotification('Uji', 'Pesan', '//situs-jahat.test/login'));
    $id = $d['mUser']->notifications()->first()->id;

    $res = $this->actingAs($d['mUser'])->get(route('notifikasi.baca', $id));
    expect((string) $res->headers->get('Location'))->not->toContain('situs-jahat');
});

test('input user_id disisipkan tidak memindahkan profil dosen/tendik ke akun lain', function () {
    $d = $this->seedAkademik();
    $u = $this->buatUser('Staf', 'staf@x.test', ['administrasi_umum']);
    $t = Tendik::create(['user_id' => $u->id, 'nama_lengkap' => 'Staf', 'nik' => '9201010101010001', 'jenis_kelamin' => 'L', 'unit_kerja' => 'BAAK', 'jabatan' => 'Staf']);

    $this->actingAs($d['admin'])->put(route('admin.tendik.update', $t), [
        'name' => 'Staf', 'email' => 'staf@x.test', 'role_id' => $this->role('administrasi_umum')->id, 'nik' => '9201010101010001', 'unit_kerja' => 'BAAK',
        'user_id' => $d['admin']->id,
    ])->assertSessionHas('success');
    $this->actingAs($d['admin'])->put(route('admin.dosen.update', $d['dosen']), [
        'nama_lengkap' => 'Dosen A', 'email' => 'dosen@x.test', 'jenis_pengajar' => 'Dosen', 'nik' => '9201010101010002', 'jenis_kelamin' => 'L',
        'status_kepegawaian' => 'Tetap', 'user_id' => $d['admin']->id,
    ])->assertSessionHas('success');

    expect((int) $t->fresh()->user_id)->toBe($u->id)->and((int) $d['dosen']->fresh()->user_id)->toBe($d['dUser']->id);
});

test('pegawai dengan riwayat absensi tidak dapat dihapus (riwayat tidak ikut terhapus)', function () {
    $d = $this->seedAkademik();
    $u = $this->buatUser('Staf', 'staf@x.test', ['administrasi_umum']);
    $t = Tendik::create(['user_id' => $u->id, 'nama_lengkap' => 'Staf', 'nik' => '9201010101010001', 'jenis_kelamin' => 'L', 'unit_kerja' => 'BAAK', 'jabatan' => 'Staf']);
    $lok = LokasiKerja::create(['nama_lokasi' => 'Kampus', 'latitude' => 0, 'longitude' => 0, 'radius_toleransi_meter' => 50]);
    AbsensiPegawai::create(['user_id' => $u->id, 'lokasi_kerja_id' => $lok->id, 'tanggal_absensi' => now()->toDateString(), 'status_kehadiran' => 'Hadir']);

    $this->actingAs($d['admin'])->delete(route('admin.tendik.destroy', $t))->assertSessionHas('error');
    $this->actingAs($d['admin'])->delete(route('admin.user.destroy', $u))->assertSessionHas('error');
    expect(User::find($u->id))->not->toBeNull()->and(AbsensiPegawai::count())->toBe(1);

    // pegawai tanpa riwayat tetap bisa dihapus bersih
    $u2 = $this->buatUser('Baru', 'baru@x.test', ['administrasi_umum']);
    $t2 = Tendik::create(['user_id' => $u2->id, 'nama_lengkap' => 'Baru', 'nik' => '9201010101010003', 'jenis_kelamin' => 'P', 'unit_kerja' => 'BAAK', 'jabatan' => 'Staf']);
    $this->actingAs($d['admin'])->delete(route('admin.tendik.destroy', $t2))->assertSessionHas('success');
    expect(User::find($u2->id))->toBeNull()->and(Tendik::find($t2->id))->toBeNull();
});

test('perintah kosongkan data meminta konfirmasi dan mengosongkan tabel baru', function () {
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d);

    $this->artisan('app:kosongkan-data')->expectsConfirmation('Yakin ingin mengosongkan data? Tindakan ini tidak dapat dibatalkan.', 'no')->assertSuccessful();
    expect(\App\Models\KrsPersetujuan::count())->toBe(1);

    $this->artisan('app:kosongkan-data', ['--force' => true])->assertSuccessful();
    expect(\App\Models\KrsPersetujuan::count())->toBe(0)->and(Mahasiswa::count())->toBe(0)
        ->and(User::count())->toBe(1)->and(User::first()->hasRole('admin'))->toBeTrue();
});
