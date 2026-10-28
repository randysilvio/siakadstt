<?php

use App\Models\BimbinganLog;
use App\Models\BimbinganTugasAkhir;
use App\Models\Camaba;
use App\Models\DokumenTerbit;
use App\Models\EvaluasiPertanyaan;
use App\Models\EvaluasiSesi;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\PengajuanSurat;
use App\Models\PmbKepanitiaan;
use App\Models\PmbPeriod;
use App\Models\User;
use App\Models\VerumKelas;
use App\Models\VerumPresensi;
use App\Models\VerumTugas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

/**
 * Satu siklus hidup lengkap mahasiswa melalui antarmuka (HTTP), dari pendaftaran PMB hingga transkrip alumni.
 * Setiap langkah memakai peran yang semestinya dan memeriksa hasilnya di basis data.
 */
test('siklus lengkap: PMB → keuangan → KRS → perkuliahan → nilai → EDOM → KHS → surat → bimbingan → lulus', function () {
    Storage::fake('local');
    Storage::fake('public');
    $d = $this->seedAkademik();
    $keu = $this->buatUser('Keuangan', 'keu@x.test', ['keuangan']);
    $panitia = $this->buatUser('Panitia', 'panitia@x.test', ['administrasi_umum']);
    $bau = $this->buatUser('BAU', 'bau@x.test', ['administrasi_umum']);
    $kUser = $this->buatUser('Kaprodi', 'kap@x.test', ['dosen']);
    $kaprodi = \App\Models\Dosen::create(['user_id' => $kUser->id, 'nidn' => '999', 'nama_lengkap' => 'Kaprodi', 'jenis_pengajar' => 'Dosen', 'program_studi_id' => $d['prodi']->id]);
    $d['prodi']->update(['kaprodi_dosen_id' => $kaprodi->id]);
    $tahunPmb = (int) now()->year;

    // ===== 1. Admin membuka gelombang PMB & menunjuk panitia
    $this->actingAs($d['admin'])->post(route('admin.pmb-periods.store'), [
        'nama_gelombang' => 'Gelombang I', 'tahun_pmb' => $tahunPmb, 'tanggal_buka' => now()->subDay()->toDateString(),
        'tanggal_tutup' => now()->addMonth()->toDateString(), 'biaya_pendaftaran' => 250000, 'jenis_ujian' => 'offline', 'is_active' => 1,
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.pmb-periods.index'));
    $periode = PmbPeriod::firstOrFail();
    $this->actingAs($d['admin'])->post(route('admin.pmb-panitia.store'), [
        'tahun_pmb' => $tahunPmb, 'jabatan' => 'Ketua', 'mode' => 'akun_lama', 'user_id' => $panitia->id,
    ])->assertSessionHas('success');

    // ===== 2. Calon mendaftar, mengunggah bukti bayar, panitia memverifikasi
    app('auth')->forgetGuards();
    $this->post(route('pmb.register.store'), [
        'name' => 'Yohanes Calon', 'email' => 'calon@x.test', 'no_hp' => '081234', 'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123',
        'pmb_period_id' => $periode->id, 'pilihan_prodi_1_id' => $d['prodi']->id,
    ])->assertRedirect(route('dashboard'));
    $calon = User::where('email', 'calon@x.test')->firstOrFail();
    $tagihanPmb = Pembayaran::where('user_id', $calon->id)->where('jenis_pembayaran', 'formulir_pmb')->firstOrFail();

    $this->actingAs($calon)->get(route('dashboard'))->assertOk();
    $this->actingAs($calon)->post(route('pmb.pembayaran.store'), ['pembayaran_id' => $tagihanPmb->id, 'bukti_bayar' => UploadedFile::fake()->image('bukti.jpg')])
        ->assertSessionHas('success');
    expect($tagihanPmb->fresh()->status)->toBe('menunggu_konfirmasi');

    $this->actingAs($panitia)->post(route('admin.pmb.payment.approve', $tagihanPmb->id))->assertSessionHas('success');
    expect($tagihanPmb->fresh()->status)->toBe('lunas');

    // ===== 3. Calon melengkapi biodata & berkas, mencetak kartu ujian; panitia meluluskan
    $this->actingAs($calon)->get(route('pmb.biodata.show'))->assertOk();
    $this->actingAs($calon)->put(route('pmb.biodata.update'), [
        'tempat_lahir' => 'Fakfak', 'tanggal_lahir' => '2007-05-01', 'jenis_kelamin' => 'L', 'agama' => 'Kristen Protestan',
        'alamat' => 'Jl. Merdeka', 'sekolah_asal' => 'SMA YPK', 'nisn' => '0071234567', 'tahun_lulus' => $tahunPmb, 'nilai_rata_rata_rapor' => 82,
        'pilihan_prodi_1_id' => $d['prodi']->id,
        'file_ijazah' => UploadedFile::fake()->create('ijazah.pdf', 100, 'application/pdf'),
        'file_pas_foto' => UploadedFile::fake()->image('foto.jpg'),
    ])->assertSessionHasNoErrors()->assertSessionHas('success');
    $camaba = Camaba::where('user_id', $calon->id)->firstOrFail();
    expect($camaba->status_pendaftaran)->toBe('menunggu_verifikasi')->and($camaba->documents()->count())->toBe(2);
    $this->actingAs($calon)->get(route('pmb.kartu-ujian.cetak'))->assertOk();

    $this->actingAs($panitia)->get(route('admin.pmb.show', $camaba))->assertOk();
    $this->actingAs($panitia)->put(route('admin.pmb.approve', $camaba))->assertRedirect(route('admin.pmb.index'));
    $mhs = Mahasiswa::where('user_id', $calon->id)->firstOrFail();
    expect($mhs->status_mahasiswa)->toBe('Aktif')
        ->and((int) $mhs->tahun_masuk)->toBe($tahunPmb)
        ->and($calon->fresh()->hasRole('mahasiswa'))->toBeTrue();

    // ===== 4. Admin menetapkan dosen wali
    $this->actingAs($d['admin'])->post(route('perwalian.store'), ['mahasiswa_ids' => [$mhs->id], 'dosen_wali_id' => $d['dosen']->id]);
    if ((int) $mhs->fresh()->dosen_wali_id !== $d['dosen']->id) {
        $this->actingAs($kUser)->post(route('perwalian.store'), ['mahasiswa_ids' => [$mhs->id], 'dosen_wali_id' => $d['dosen']->id]);
    }
    expect((int) $mhs->fresh()->dosen_wali_id)->toBe($d['dosen']->id);

    // ===== 5. Keuangan membuat tagihan SPP massal; mahasiswa terkunci dari KRS sampai lunas
    $calon = $calon->fresh();
    $semesterTagihan = $d['ta']->tahun . ' ' . $d['ta']->semester;
    $this->actingAs($keu)->post(route('pembayaran.storeGenerate'), [
        'semester' => $semesterTagihan, 'jumlah' => 2500000, 'jenis_pembayaran' => 'spp', 'prodi_id' => $d['prodi']->id, 'angkatan' => $tahunPmb,
    ])->assertSessionHas('success');
    $spp = Pembayaran::where('mahasiswa_id', $mhs->id)->where('jenis_pembayaran', 'spp')->firstOrFail();
    $this->actingAs($calon)->get(route('krs.index'))->assertRedirect(route('pembayaran.riwayat'));

    $this->actingAs($calon)->post(route('pembayaran.uploadBukti', $spp), ['bukti_bayar' => UploadedFile::fake()->image('spp.jpg')])
        ->assertRedirect(route('pembayaran.riwayat'));
    $this->actingAs($keu)->patch(route('pembayaran.lunas', $spp))->assertSessionHas('success');
    expect($spp->fresh()->status)->toBe('lunas');

    // ===== 6. KRS: mahasiswa mengisi, dosen wali menyetujui, KRS dicetak
    $this->actingAs($calon)->get(route('krs.index'))->assertOk()->assertSee('Dogmatika');
    $this->actingAs($calon)->post(route('krs.store'), ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'A']])
        ->assertSessionHasNoErrors()->assertSessionHas('success');
    expect($mhs->fresh()->statusKrsPada($d['ta']->id))->toBe('Menunggu Persetujuan');

    $this->actingAs($d['dUser'])->get(route('perwalian.show', $mhs))->assertOk();
    $this->actingAs($d['dUser'])->patch(route('perwalian.updateStatus', $mhs), ['status_krs' => 'Disetujui'])->assertSessionHas('success');
    expect($mhs->fresh()->krsDisetujuiPada($d['ta']->id))->toBeTrue();
    $this->actingAs($calon)->get(route('krs.cetak.final'))->assertOk();
    $this->actingAs($calon)->get(route('jadwal.cetak'))->assertOk();

    // ===== 7. Perkuliahan di Verum: kelas, presensi, tugas
    $this->actingAs($d['dUser'])->post(route('verum.store'), ['mata_kuliah_id' => $d['mk']->id, 'nama_kelas' => 'Dogmatika A', 'rombel' => 'A'])
        ->assertSessionHasNoErrors();
    $kelas = VerumKelas::firstOrFail();
    $this->actingAs($calon)->get(route('verum.show', $kelas))->assertOk();

    $this->actingAs($d['dUser'])->post(route('verum.presensi.store', $kelas), ['judul_pertemuan' => 'Pengantar', 'pertemuan_ke' => 1, 'durasi' => 60])
        ->assertSessionHasNoErrors();
    $presensi = VerumPresensi::firstOrFail();
    $this->actingAs($calon)->post(route('verum.presensi.hadir', $presensi))->assertSessionHas('success');
    expect($presensi->kehadiran()->count())->toBe(1);

    $this->actingAs($d['dUser'])->post(route('verum.tugas.store', $kelas), ['judul' => 'Esai 1', 'instruksi' => 'Tulis esai', 'tenggat_waktu' => now()->addWeek()->toDateTimeString()])
        ->assertSessionHasNoErrors();
    $tugas = VerumTugas::firstOrFail();
    $this->actingAs($calon)->post(route('verum.tugas.kumpulkan', $tugas), ['file_jawaban' => UploadedFile::fake()->create('esai.pdf', 50, 'application/pdf')])
        ->assertSessionHas('success');
    $this->actingAs($d['dUser'])->get(route('verum.tugas.rekap', $tugas))->assertOk();
    $this->actingAs($d['dUser'])->post(route('verum.tugas.nilai', $tugas), ['nilai' => [$mhs->id => 88]])->assertSessionHas('success');
    $this->actingAs($d['dUser'])->get(route('verum.presensi.cetakBap', $kelas))->assertOk();

    // ===== 8. Buku nilai: dosen menginput komponen → huruf otomatis
    $this->actingAs($d['dUser'])->get(route('nilai.show', $d['mk']))->assertOk()->assertSee($mhs->nim);
    $this->actingAs($d['dUser'])->post(route('nilai.store'), [
        'mata_kuliah_id' => $d['mk']->id, 'peserta' => [$mhs->id],
        'nilai_kehadiran' => [$mhs->id => 100], 'nilai_tugas' => [$mhs->id => 88], 'nilai_uts' => [$mhs->id => 80], 'nilai_uas' => [$mhs->id => 85],
    ])->assertSessionHasNoErrors()->assertSessionHas('success');
    $nilai = $mhs->fresh()->mataKuliahs()->wherePivot('tahun_akademik_id', $d['ta']->id)->first()->pivot->nilai;
    expect($nilai)->toBeIn(['A', 'B']);

    // ===== 9. EDOM wajib sebelum KHS; setelah diisi KHS & transkrip dapat dicetak dan diverifikasi
    $sesi = EvaluasiSesi::create(['nama_sesi' => 'EDOM', 'tahun_akademik_id' => $d['ta']->id, 'tanggal_mulai' => now()->subDay(), 'tanggal_selesai' => now()->addWeek(), 'is_active' => true]);
    $p1 = EvaluasiPertanyaan::create(['pertanyaan' => 'Dosen tepat waktu', 'tipe_jawaban' => 'skala_1_5', 'is_active' => true, 'urutan' => 1]);
    $p2 = EvaluasiPertanyaan::create(['pertanyaan' => 'Saran', 'tipe_jawaban' => 'teks', 'is_active' => true, 'urutan' => 2]);
    $this->actingAs($calon)->get(route('khs.index'))->assertRedirect(route('evaluasi.index'));
    $this->actingAs($calon)->get(route('evaluasi.show', $d['mk']))->assertOk();
    $this->actingAs($calon)->post(route('evaluasi.store', $d['mk']), ['jawaban' => [$p1->id => 4, $p2->id => 'Baik']])->assertSessionHas('success');

    $calon = $calon->fresh(); // tiap request nyata memuat ulang akun; status EDOM di-memo per instance
    $this->actingAs($calon)->get(route('khs.index'))->assertOk();
    $this->actingAs($calon)->get(route('khs.cetak', ['tahun_akademik_id' => $d['ta']->id]))->assertOk();
    $this->actingAs($calon)->get(route('transkrip.cetak'))->assertOk();
    $kode = DokumenTerbit::where('jenis', 'KHS')->where('mahasiswa_id', $mhs->id)->latest('id')->value('kode');
    $this->get(route('verifikasi.dokumen', $kode))->assertOk()->assertSee($mhs->nim);
    $this->actingAs($d['admin'])->get(route('admin.evaluasi-hasil.show', [$sesi->id, $d['dosen']->id]))->assertOk();

    // ===== 10. Layanan surat: ajukan → BAAK setujui → cetak → QR terverifikasi
    $this->actingAs($calon)->post(route('surat-mandiri.store'), ['jenis_surat' => 'Surat Keterangan Aktif Kuliah', 'keperluan' => 'Beasiswa'])->assertSessionHas('success');
    $surat = PengajuanSurat::firstOrFail();
    $this->actingAs($bau)->patch(route('admin.surat-mandiri.updateStatus', $surat), ['status' => 'Disetujui'])->assertSessionHas('success');
    $surat->refresh();
    expect($surat->status)->toBe('Disetujui')->and($surat->nomor_surat)->not->toBeEmpty()->and($surat->tanggal_disetujui)->not->toBeNull();
    $this->actingAs($calon)->get(route('surat-mandiri.cetak', $surat))->assertOk();
    $this->get(route('verifikasi.dokumen', $surat->kode_verifikasi))->assertOk()->assertSee($mhs->nim);

    // ===== 11. Bimbingan tugas akhir: ajukan → Kaprodi tetapkan pembimbing → log → paraf
    $this->actingAs($calon)->post(route('bimbingan.store'), ['jenis_bimbingan' => 'Skripsi / Tugas Akhir', 'judul' => 'Teologi Kontekstual di Fakfak'])->assertSessionHas('success');
    $ta = BimbinganTugasAkhir::firstOrFail();
    $this->actingAs($kUser)->patch(route('bimbingan.updateStatus', $ta), ['status' => 'Disetujui / Aktif Bimbingan', 'pembimbing_1_id' => $d['dosen']->id])->assertSessionHas('success');
    expect($ta->fresh()->pembimbing_1_id)->toBe($d['dosen']->id);
    $this->actingAs($calon)->post(route('bimbingan.log.store', $ta), ['tanggal_bimbingan' => now()->toDateString(), 'topik_bab' => 'Bab I',
        'file_draf' => UploadedFile::fake()->create('bab1.pdf', 80, 'application/pdf')])->assertSessionHas('success');
    $log = BimbinganLog::firstOrFail();
    $this->actingAs($d['dUser'])->patch(route('bimbingan.log.validasi', $log), ['status_paraf' => 'Disetujui / Lanjut', 'arahan_dosen' => 'Lanjut Bab II'])->assertSessionHas('success');
    expect($log->fresh()->status_paraf)->toBe('Disetujui / Lanjut');
    $this->actingAs($calon)->get(route('bimbingan.cetak', $ta))->assertOk();

    // ===== 12. Kelulusan: admin menetapkan Lulus → alumni wajib tracer study sebelum transkrip akhir
    $this->actingAs($d['admin'])->put(route('admin.mahasiswa.update', $mhs), [
        'nim' => $mhs->nim, 'nik' => '9201010101070001', 'nama_lengkap' => $mhs->nama_lengkap, 'email' => $calon->email,
        'program_studi_id' => $d['prodi']->id, 'tahun_masuk' => $tahunPmb, 'nama_ibu_kandung' => 'Maria', 'status_mahasiswa' => 'Lulus',
        'tanggal_lulus' => now()->toDateString(),
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.mahasiswa.index'));
    $calon = $calon->fresh();
    $this->actingAs($calon)->get(route('transkrip.cetak'))->assertRedirect(route('tracer.index'));
    $this->actingAs($calon)->post(route('tracer.store'), ['tahun_lulus' => now()->year, 'status_pekerjaan' => 'Bekerja', 'waktu_tunggu_bulan' => 2,
        'kesesuaian_bidang' => 'Sesuai'])->assertRedirect(route('tracer.index'));
    $this->actingAs($calon)->get(route('transkrip.cetak'))->assertOk();
});
