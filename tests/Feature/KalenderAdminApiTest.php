<?php

use App\Models\KegiatanAkademik;
use App\Models\Pengaturan;
use App\Models\Role;
use App\Models\TahunAkademik;
use App\Models\User;

function adminApi(): User
{
    $u = User::factory()->create();
    $u->roles()->attach(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']));
    return $u;
}

test('hanya admin yang dapat membuka panel kalender di aplikasi', function () {
    $this->actingAs(User::factory()->create(), 'sanctum')->getJson('/api/admin/kalender')->assertForbidden();
    $this->actingAs(adminApi(), 'sanctum')->getJson('/api/admin/kalender')->assertOk()->assertJsonStructure(['data', 'ringkasan' => ['saklar_krs', 'jenis']]);
});

test('acara KRS dari aplikasi menerapkan tanggal ke tahun akademik dan menolak bentrok', function () {
    $admin = adminApi();
    $ta = TahunAkademik::create(['tahun' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true, 'tanggal_mulai_krs' => '2026-10-01', 'tanggal_selesai_krs' => '2026-10-10']);
    $peran = Role::firstOrCreate(['name' => 'mahasiswa'], ['display_name' => 'Mahasiswa']);

    $isi = ['judul_kegiatan' => 'Pengisian KRS', 'tanggal_mulai' => '2026-10-20', 'tanggal_selesai' => '2026-11-01',
        'target_roles' => [$peran->id], 'jenis' => 'krs', 'tahun_akademik_id' => $ta->id];
    $res = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/kalender', $isi)->assertCreated();
    expect($ta->fresh()->tanggal_selesai_krs)->toStartWith('2026-11-01')
        ->and($res->json('data.pengendali'))->toBeTrue();

    $this->actingAs($admin, 'sanctum')->postJson('/api/admin/kalender', $isi)->assertStatus(422)->assertJsonValidationErrors('jenis');

    $id = $res->json('data.id');
    $this->actingAs($admin, 'sanctum')->putJson("/api/admin/kalender/{$id}", array_merge($isi, ['tanggal_selesai' => '2026-11-05']))->assertOk();
    expect($ta->fresh()->tanggal_selesai_krs)->toStartWith('2026-11-05');

    $this->actingAs($admin, 'sanctum')->deleteJson("/api/admin/kalender/{$id}")->assertOk();
    expect(KegiatanAkademik::count())->toBe(0);
});

test('saklar darurat KRS dari aplikasi', function () {
    $this->actingAs(adminApi(), 'sanctum')->postJson('/api/admin/kalender/saklar-krs', ['status' => 'tutup'])
        ->assertOk()->assertJsonPath('ringkasan.saklar_krs', 'tutup');
    expect(Pengaturan::get('status_krs'))->toBe('tutup');
});

test('opsi formulir memuat jenis, peran, tahun akademik', function () {
    $this->actingAs(adminApi(), 'sanctum')->getJson('/api/admin/kalender/opsi')->assertOk()
        ->assertJsonStructure(['jenis' => [['kunci', 'label', 'butuh']], 'peran', 'tahun_akademik', 'pmb']);
});
