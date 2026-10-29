<?php

use App\Models\PerangkatPush;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\Push\AntrianPush;
use App\Services\Push\PushFcm;
use Illuminate\Support\Facades\Http;

afterEach(fn () => array_map('unlink', glob(storage_path('framework/testing/fcm-*.json')) ?: []));

function kunciFcmPalsu(): string
{
    $kunci = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($kunci, $pem);
    $jalur = storage_path('framework/testing/fcm-' . uniqid() . '.json');
    @mkdir(dirname($jalur), 0777, true);
    file_put_contents($jalur, json_encode([
        'type' => 'service_account', 'project_id' => 'stt-gpi-papua', 'private_key' => $pem,
        'client_email' => 'siakad@stt-gpi-papua.iam.gserviceaccount.com', 'token_uri' => 'https://oauth2.googleapis.com/token',
    ]));
    return $jalur;
}

function aktifkanFcm(): void
{
    config(['services.fcm.credentials' => kunciFcmPalsu()]);
    app()->forgetInstance(PushFcm::class);
    app()->forgetInstance(AntrianPush::class);
}

test('aplikasi dapat mendaftarkan dan menghapus token perangkat', function () {
    $u = User::factory()->create();
    $this->actingAs($u, 'sanctum')->postJson('/api/push/token', ['token' => 'tok-hp-1', 'platform' => 'android', 'versi' => '1.2.0'])
        ->assertOk()->assertJsonPath('push_aktif', false);
    expect(PerangkatPush::where('user_id', $u->id)->count())->toBe(1);

    // Token yang sama dipakai akun lain di HP yang sama → pindah pemilik, tidak dobel
    $lain = User::factory()->create();
    $this->actingAs($lain, 'sanctum')->postJson('/api/push/token', ['token' => 'tok-hp-1'])->assertOk();
    expect(PerangkatPush::where('token', 'tok-hp-1')->value('user_id'))->toBe($lain->id);

    $this->actingAs($lain, 'sanctum')->deleteJson('/api/push/token', ['token' => 'tok-hp-1'])->assertOk();
    expect(PerangkatPush::count())->toBe(0);
});

test('tanpa kunci service account, notifikasi tetap tersimpan dan tidak ada push', function () {
    Http::fake();
    $u = User::factory()->create();
    PerangkatPush::daftarkan($u, 'tok-a');
    $u->notify(new GeneralNotification('KRS Disetujui', 'KRS Anda disetujui.'));

    expect($u->notifications()->count())->toBe(1)->and(app(AntrianPush::class)->jumlah())->toBe(0);
    Http::assertNothingSent();
});

test('notifikasi database dikirim sebagai push FCM dan token mati dibuang', function () {
    aktifkanFcm();
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'akses-123', 'expires_in' => 3599]),
        'fcm.googleapis.com/*' => function ($req) {
            return $req['message']['token'] === 'tok-mati'
                ? Http::response(['error' => ['code' => 404, 'status' => 'NOT_FOUND', 'details' => [['errorCode' => 'UNREGISTERED']]]], 404)
                : Http::response(['name' => 'projects/stt-gpi-papua/messages/1']);
        },
    ]);

    $u = User::factory()->create();
    PerangkatPush::daftarkan($u, 'tok-hidup', 'android');
    PerangkatPush::daftarkan($u, 'tok-mati', 'android');
    PerangkatPush::daftarkan($u, 'tok-web', 'web');

    $u->notify(new GeneralNotification('Tugas baru', '<b>Kerjakan</b> tugas 1', '/verum'));
    $hasil = app(AntrianPush::class)->kirimSemua();

    expect($hasil)->toBe(['terkirim' => 2, 'gagal' => 1, 'dibuang' => 1])
        ->and(PerangkatPush::pluck('token')->sort()->values()->all())->toBe(['tok-hidup', 'tok-web']);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'projects/stt-gpi-papua/messages:send')
        && $r->hasHeader('Authorization', 'Bearer akses-123')
        && $r['message']['notification']['body'] === 'Kerjakan tugas 1'
        && $r['message']['data']['jenis'] === 'notifikasi'
        && ($r['message']['token'] !== 'tok-hidup' || $r['message']['android']['notification']['channel_id'] === 'siakad')
        && ($r['message']['token'] !== 'tok-web' || str_ends_with($r['message']['webpush']['fcm_options']['link'], '/verum')));
    // Token akses diambil sekali lalu dipakai ulang
    Http::assertSentCount(4);
});

test('keluar dari aplikasi menghapus token perangkat tersebut', function () {
    $u = User::factory()->create();
    PerangkatPush::daftarkan($u, 'tok-keluar');
    $this->actingAs($u, 'sanctum')->postJson('/api/logout', ['push_token' => 'tok-keluar'])->assertOk();
    expect(PerangkatPush::count())->toBe(0);
});

test('token web lama dari simpan-fcm-token ikut tercatat sebagai perangkat', function () {
    $u = User::factory()->create();
    $this->actingAs($u)->postJson(route('fcmToken.simpan'), ['token' => 'tok-browser'])->assertOk();
    expect(PerangkatPush::where('token', 'tok-browser')->value('platform'))->toBe('web');
});
