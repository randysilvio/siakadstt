<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use App\Models\VerumKelas;
use App\Models\VerumPengumpulan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedAkademik;

uses(SeedAkademik::class);

function kaprodiLain($t, array $d): array
{
    $u = $t->buatUser('Kaprodi', 'kaprodi@x.test', ['dosen']);
    $dosen = Dosen::create(['user_id' => $u->id, 'nidn' => '222', 'nama_lengkap' => 'Kaprodi TH', 'jenis_pengajar' => 'Dosen', 'program_studi_id' => $d['prodi']->id]);
    $d['prodi']->update(['kaprodi_dosen_id' => $dosen->id]);
    return [$u, $dosen];
}

test('sirkulasi KRS lengkap dari aplikasi: gerbang bayar, tolak wajib catatan, revisi, setujui, buka kembali hanya oleh penyetuju', function () {
    $d = $this->seedAkademik();
    [$kUser] = kaprodiLain($this, $d);
    $tagihan = Pembayaran::create(['mahasiswa_id' => $d['mhs']->id, 'user_id' => $d['mUser']->id, 'jumlah' => 1, 'semester' => 'x', 'status' => 'belum_lunas', 'jenis_pembayaran' => 'spp']);

    // Tagihan belum lunas: ringkasan tetap terbaca, tapi terkunci dengan alasan
    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/krs/saya')->assertOk()
        ->assertJsonPath('bisa_ubah', false)->assertJsonPath('status_krs', 'Belum Mengajukan')
        ->assertJsonPath('periode.buka', true);
    expect($this->actingAs($d['mUser'], 'sanctum')->getJson('/api/krs/saya')->json('gerbang_bayar'))->toContain('belum lunas');

    $tagihan->update(['status' => 'lunas']);
    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/krs/saya')->assertJsonPath('bisa_ubah', true);
    $this->actingAs($d['mUser'], 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'A']])->assertOk();

    // Dosen wali & Kaprodi sama-sama melihat antrean
    $this->actingAs($d['dUser'], 'sanctum')->getJson('/api/krs/mahasiswa?status=menunggu')->assertOk()
        ->assertJsonPath('ringkas.menunggu', 1)->assertJsonPath('data.0.peran', 'wali')->assertJsonPath('data.0.total_sks', 3);
    $this->actingAs($kUser, 'sanctum')->getJson('/api/krs/mahasiswa?status=menunggu')->assertJsonPath('data.0.peran', 'kaprodi');

    // Tolak tanpa catatan ditolak; dengan catatan berhasil
    $this->actingAs($kUser, 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Tolak'])->assertStatus(422);
    $this->actingAs($kUser, 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Tolak', 'catatan' => 'Ambil kelas B'])->assertOk();
    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/krs/saya')
        ->assertJsonPath('status_krs', 'Ditolak')->assertJsonPath('catatan', 'Ambil kelas B')->assertJsonPath('divalidasi_oleh', 'Kaprodi');

    // Revisi mahasiswa → kembali menunggu; dosen wali menyetujui
    $this->actingAs($d['mUser'], 'sanctum')->postJson('/api/krs/submit', ['mata_kuliahs' => [$d['mk']->id], 'nama_kelas' => [$d['mk']->id => 'B']])->assertOk();
    $this->actingAs($d['dUser'], 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Setujui'])->assertOk();
    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/krs/saya')
        ->assertJsonPath('status_krs', 'Disetujui')->assertJsonPath('bisa_ubah', false)->assertJsonPath('bisa_cetak', true)
        ->assertJsonPath('mata_kuliah.0.kelas', 'B')->assertJsonPath('mata_kuliah.0.jadwal.0.hari', 'Rabu');

    // Kaprodi tidak bisa membuka persetujuan dosen wali (dan sebaliknya); penyetuju bisa
    $this->actingAs($kUser, 'sanctum')->getJson("/api/krs/validasi/{$d['mhs']->id}")
        ->assertJsonPath('bisa_buka_kembali', false)->assertJsonPath('bisa_validasi', false);
    $this->actingAs($kUser, 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Buka'])->assertStatus(422);
    $this->actingAs($d['dUser'], 'sanctum')->getJson("/api/krs/validasi/{$d['mhs']->id}")->assertJsonPath('bisa_buka_kembali', true);
    $this->actingAs($d['dUser'], 'sanctum')->postJson("/api/krs/validasi/{$d['mhs']->id}", ['status' => 'Buka'])->assertOk()
        ->assertJsonPath('status_krs', 'Menunggu Persetujuan');

    // Dosen wali menghapus MK dari KRS yang belum disetujui → kosong = Belum Mengajukan; Kaprodi tidak boleh
    $this->actingAs($kUser, 'sanctum')->deleteJson("/api/krs/validasi/{$d['mhs']->id}/mk/{$d['mk']->id}")->assertForbidden();
    $this->actingAs($d['dUser'], 'sanctum')->deleteJson("/api/krs/validasi/{$d['mhs']->id}/mk/{$d['mk']->id}")->assertOk();
    expect($d['mhs']->fresh()->statusKrsPada($d['ta']->id))->toBe('Belum Mengajukan');

    // Dosen tanpa wewenang
    $lain = $this->buatUser('Lain', 'lain@x.test', ['dosen']);
    Dosen::create(['user_id' => $lain->id, 'nidn' => '7', 'nama_lengkap' => 'Lain', 'jenis_pengajar' => 'Dosen']);
    $this->actingAs($lain, 'sanctum')->getJson("/api/krs/validasi/{$d['mhs']->id}")->assertForbidden();
    $this->actingAs($lain, 'sanctum')->getJson('/api/krs/mahasiswa?status=semua')->assertJsonPath('ringkas.semua', 0);
});

test('KRS tetap terbaca saat periode ditutup, dan PDF KRS hanya setelah disetujui', function () {
    $d = $this->seedAkademik();
    $d['mhs']->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'A']);
    $d['mhs']->setStatusKrs($d['ta']->id, 'Menunggu Persetujuan');
    Pengaturan::set('status_krs', 'tutup');

    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/krs/saya')->assertOk()
        ->assertJsonPath('periode.buka', false)->assertJsonPath('bisa_ubah', false)->assertJsonPath('total_sks', 3);
    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/dokumen/krs')->assertStatus(422);

    $d['mhs']->setStatusKrs($d['ta']->id, 'Disetujui', null, $d['dUser']->id);
    $r = $this->actingAs($d['mUser'], 'sanctum')->get('/api/dokumen/krs');
    $r->assertOk();
    expect($r->headers->get('content-type'))->toContain('pdf');
});

test('Verum penuh lewat API: kelas per rombel, materi, tugas, pengumpulan, nilai, presensi, forum', function () {
    Storage::fake('local');
    Storage::fake('public');
    $d = $this->seedAkademik();
    $this->ambilDanSetujui($d, null, 'A');

    // Dosen membuat kelas rombel A; rombel tak terdaftar ditolak
    $this->actingAs($d['dUser'], 'sanctum')->getJson('/api/verum/kelas-form')->assertOk()->assertJsonPath('0.rombel', ['A', 'B']);
    $this->actingAs($d['dUser'], 'sanctum')->postJson('/api/verum/kelas', ['mata_kuliah_id' => $d['mk']->id, 'nama_kelas' => 'Dogmatika C', 'rombel' => 'C'])->assertStatus(422);
    $id = $this->actingAs($d['dUser'], 'sanctum')->postJson('/api/verum/kelas', ['mata_kuliah_id' => $d['mk']->id, 'nama_kelas' => 'Dogmatika A', 'rombel' => 'a'])->assertOk()->json('id');
    $kelas = VerumKelas::find($id);
    expect($kelas->rombel)->toBe('A');

    // Mahasiswa rombel A melihat kelas; mahasiswa rombel B tidak
    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/verum/kelas')->assertOk()->assertJsonPath('data.0.id', $id)->assertJsonPath('peran', 'mahasiswa');
    $bUser = $this->buatUser('Mhs B', 'mhsb@x.test', ['mahasiswa']);
    $mhsB = Mahasiswa::create(['user_id' => $bUser->id, 'nim' => '2026TH002', 'nama_lengkap' => 'Mhs B', 'program_studi_id' => $d['prodi']->id, 'status_mahasiswa' => 'Aktif', 'tahun_masuk' => 2026, 'dosen_wali_id' => $d['dosen']->id]);
    $mhsB->mataKuliahs()->attach($d['mk']->id, ['tahun_akademik_id' => $d['ta']->id, 'nama_kelas' => 'B']);
    $mhsB->setStatusKrs($d['ta']->id, 'Disetujui', null, $d['dUser']->id);
    $this->actingAs($bUser, 'sanctum')->getJson('/api/verum/kelas')->assertJsonCount(0, 'data');
    $this->actingAs($bUser, 'sanctum')->getJson("/api/verum/kelas/{$id}")->assertForbidden();

    // Materi (wajib file atau tautan) & tugas oleh dosen; mahasiswa tidak boleh
    $this->actingAs($d['dUser'], 'sanctum')->postJson("/api/verum/kelas/{$id}/materi", ['judul' => 'Kosong'])->assertStatus(422);
    $this->actingAs($d['dUser'], 'sanctum')->post("/api/verum/kelas/{$id}/materi", ['judul' => 'Silabus', 'file_materi' => UploadedFile::fake()->create('silabus.pdf', 100, 'application/pdf')], ['Accept' => 'application/json'])->assertOk();
    $this->actingAs($d['mUser'], 'sanctum')->postJson("/api/verum/kelas/{$id}/tugas", ['judul' => 'x', 'instruksi' => 'y', 'tenggat_waktu' => now()->addDay()->toDateTimeString()])->assertForbidden();
    $this->actingAs($d['dUser'], 'sanctum')->postJson("/api/verum/kelas/{$id}/tugas", ['judul' => 'Esai', 'instruksi' => 'Tulis 2 halaman', 'tenggat_waktu' => now()->addDay()->toDateTimeString()])->assertOk();
    $tugasId = $kelas->tugas()->first()->id;

    // Mahasiswa mengumpulkan; dosen melihat rekap & memberi nilai; mahasiswa melihat nilai
    $this->actingAs($d['mUser'], 'sanctum')->post("/api/verum/tugas/{$tugasId}/kumpulkan", ['file_jawaban' => UploadedFile::fake()->create('esai.pdf', 200, 'application/pdf')], ['Accept' => 'application/json'])->assertOk();
    $rekap = $this->actingAs($d['dUser'], 'sanctum')->getJson("/api/verum/tugas/{$tugasId}")->assertOk()
        ->assertJsonPath('peran', 'dosen')->assertJsonPath('peserta.0.dikumpulkan', true);
    $berkasId = $rekap->json('peserta.0.berkas_id');
    $this->actingAs($d['dUser'], 'sanctum')->get("/api/berkas/jawaban-tugas/{$berkasId}")->assertOk();
    $this->actingAs($bUser, 'sanctum')->get("/api/berkas/jawaban-tugas/{$berkasId}")->assertForbidden();
    $this->actingAs($d['dUser'], 'sanctum')->postJson("/api/verum/tugas/{$tugasId}/nilai", ['nilai' => [$d['mhs']->id => 88, $mhsB->id => 100], 'catatan_dosen' => [$d['mhs']->id => 'Baik']])->assertOk();
    expect(VerumPengumpulan::where('mahasiswa_id', $mhsB->id)->exists())->toBeFalse(); // bukan peserta rombel A
    $this->actingAs($d['mUser'], 'sanctum')->getJson("/api/verum/kelas/{$id}")->assertOk()
        ->assertJsonPath('tugas.0.saya.nilai', 88)->assertJsonPath('tugas.0.saya.catatan', 'Baik')
        ->assertJsonPath('materi.0.judul', 'Silabus');

    // Presensi + jurnal BAP; hadir dua kali = peringatan
    $pid = $this->actingAs($d['dUser'], 'sanctum')->postJson("/api/verum/kelas/{$id}/presensi", ['judul_pertemuan' => 'Pengantar', 'pertemuan_ke' => 1, 'durasi' => 30])->assertOk()->json('id');
    $this->actingAs($d['mUser'], 'sanctum')->postJson("/api/verum/presensi/{$pid}/hadir")->assertOk()->assertJsonPath('jenis', 'success');
    $this->actingAs($d['mUser'], 'sanctum')->postJson("/api/verum/presensi/{$pid}/hadir")->assertOk()->assertJsonPath('jenis', 'warning');
    $this->actingAs($d['dUser'], 'sanctum')->getJson("/api/verum/presensi/{$pid}")->assertJsonPath('jumlah_hadir', 1)->assertJsonPath('peserta.0.hadir', true);
    $this->actingAs($d['dUser'], 'sanctum')->patchJson("/api/verum/presensi/{$pid}/jurnal", ['judul_pertemuan' => 'Pengantar Dogmatika', 'materi_aktual' => 'Bab 1'])->assertOk();
    $this->actingAs($d['dUser'], 'sanctum')->getJson("/api/verum/kelas/{$id}")->assertJsonPath('pertemuan_berikut', 2)
        ->assertJsonPath('presensi.0.judul_pertemuan', 'Pengantar Dogmatika');

    // Vicon & forum
    $this->actingAs($d['dUser'], 'sanctum')->patchJson("/api/verum/kelas/{$id}/vicon", ['aktif' => true])->assertJsonPath('vicon.aktif', true);
    $this->actingAs($d['mUser'], 'sanctum')->postJson("/api/verum/kelas/{$id}/forum", ['konten' => 'Selamat pagi'])->assertOk();
    $post = $this->actingAs($d['mUser'], 'sanctum')->getJson("/api/verum/kelas/{$id}")->assertJsonPath('vicon.aktif', true)->json('postingan.0');
    expect($post['bisa_hapus'])->toBeTrue();
    $this->actingAs($bUser, 'sanctum')->deleteJson("/api/verum/forum/{$post['id']}")->assertForbidden();
    $this->actingAs($d['dUser'], 'sanctum')->deleteJson("/api/verum/forum/{$post['id']}")->assertOk();
});

test('KTM memakai desain tetap walau tema sentral diganti', function () {
    $d = $this->seedAkademik();
    $this->actingAs($d['mUser'], 'sanctum')->getJson('/api/ktm')->assertOk()
        ->assertJsonPath('nim', '2026TH001')->assertJsonPath('warna.ungu', '#3D2350')->assertJsonPath('aktif', true);

    Pengaturan::set('theme_preset', 'crimson_maroon');
    $html = $this->actingAs($d['mUser'])->get(route('dashboard'))->assertOk()->getContent();
    expect($html)->toContain('ktm-sttgpi')->toContain('images/ktm/latar-ktm.jpg')->toContain('--ktm-ungu: #3D2350')->toContain('#7f1d1d');
});
