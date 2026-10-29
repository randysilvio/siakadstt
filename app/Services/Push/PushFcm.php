<?php

namespace App\Services\Push;

use App\Models\PerangkatPush;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pengirim push lewat Firebase Cloud Messaging HTTP v1 (tanpa paket tambahan).
 *
 * Aktif hanya bila file kunci service account Firebase ada (config services.fcm.credentials).
 * Tanpa file itu, SIAKAD berjalan seperti biasa: notifikasi tetap masuk lonceng/daftar notifikasi.
 * Token yang ditolak FCM (aplikasi dihapus, token kedaluwarsa) dibuang otomatis.
 */
class PushFcm
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    private const KUNCI_CACHE = 'siakad-fcm-akses-token';

    private ?array $akun = null;

    public function aktif(): bool
    {
        return $this->akun() !== null;
    }

    public function projectId(): ?string
    {
        return config('services.fcm.project_id') ?: ($this->akun()['project_id'] ?? null);
    }

    /** @return array|null isi service-account.json */
    public function akun(): ?array
    {
        if ($this->akun !== null) {
            return $this->akun ?: null;
        }
        $jalur = config('services.fcm.credentials');
        $isi = ($jalur && is_file($jalur) && is_readable($jalur)) ? json_decode((string) file_get_contents($jalur), true) : null;
        $sah = is_array($isi) && !empty($isi['client_email']) && !empty($isi['private_key']);
        $this->akun = $sah ? $isi : [];
        return $sah ? $isi : null;
    }

    /** Token akses OAuth2 (berlaku 60 menit, disimpan 50 menit). */
    public function aksesToken(): ?string
    {
        $akun = $this->akun();
        if (!$akun) {
            return null;
        }
        if ($ada = Cache::get(self::KUNCI_CACHE)) {
            return $ada;
        }

        $kini = time();
        $b64 = fn (string $s) => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
        $tokenUri = $akun['token_uri'] ?? 'https://oauth2.googleapis.com/token';
        $kepala = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $klaim = $b64(json_encode(['iss' => $akun['client_email'], 'scope' => self::SCOPE, 'aud' => $tokenUri, 'iat' => $kini, 'exp' => $kini + 3600]));
        $tandaTangan = '';
        if (!openssl_sign("{$kepala}.{$klaim}", $tandaTangan, $akun['private_key'], OPENSSL_ALGO_SHA256)) {
            Log::warning('FCM: kunci privat service account tidak dapat dipakai.');
            return null;
        }

        $res = Http::asForm()->timeout(15)->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => "{$kepala}.{$klaim}." . $b64($tandaTangan),
        ]);
        if (!$res->successful() || !$res->json('access_token')) {
            Log::warning('FCM: gagal mengambil token akses', ['status' => $res->status(), 'isi' => mb_substr($res->body(), 0, 300)]);
            return null;
        }
        $token = $res->json('access_token');
        Cache::put(self::KUNCI_CACHE, $token, now()->addSeconds(max(60, min(3000, (int) $res->json('expires_in', 3600) - 600))));
        return $token;
    }

    /**
     * Kirim satu pesan ke sekumpulan perangkat. @param iterable<PerangkatPush> $perangkat
     * @return array{terkirim:int, gagal:int, dibuang:int}
     */
    public function kirim(iterable $perangkat, string $judul, string $isi, array $data = []): array
    {
        $hasil = ['terkirim' => 0, 'gagal' => 0, 'dibuang' => 0];
        $daftar = collect($perangkat)->values();
        if ($daftar->isEmpty() || !($akses = $this->aksesToken()) || !($proyek = $this->projectId())) {
            return $hasil;
        }

        $ujung = "https://fcm.googleapis.com/v1/projects/{$proyek}/messages:send";
        $data = array_map('strval', array_filter($data, fn ($v) => $v !== null));
        $judul = mb_substr(strip_tags($judul), 0, 120);
        $isi = mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($isi))), 0, 240);
        $dibuang = [];

        foreach ($daftar->chunk(50) as $potongan) {
            $potongan = $potongan->values();
            $respons = Http::pool(fn ($pool) => $potongan->map(fn (PerangkatPush $p) => $pool->withToken($akses)->timeout(15)
                ->post($ujung, ['message' => $this->pesan($p, $judul, $isi, $data)]))->all());

            foreach ($respons as $i => $r) {
                if ($r instanceof \Throwable || !method_exists($r, 'successful')) {
                    $hasil['gagal']++;
                    continue;
                }
                if ($r->successful()) {
                    $hasil['terkirim']++;
                    continue;
                }
                $hasil['gagal']++;
                if ($this->tokenMati($r->status(), (array) $r->json('error', []))) {
                    $dibuang[] = $potongan[$i]->id;
                } elseif ($r->status() === 401) {
                    Cache::forget(self::KUNCI_CACHE);
                }
            }
        }

        if ($dibuang) {
            $hasil['dibuang'] = PerangkatPush::whereIn('id', $dibuang)->delete();
        }
        if ($hasil['gagal'] > $hasil['dibuang']) {
            Log::info('FCM: sebagian push gagal', $hasil);
        }
        return $hasil;
    }

    private function pesan(PerangkatPush $p, string $judul, string $isi, array $data): array
    {
        $pesan = ['token' => $p->token, 'notification' => ['title' => $judul, 'body' => $isi], 'data' => $data];
        if ($p->platform === 'web') {
            $pesan['webpush'] = [
                'notification' => ['icon' => url('/images/logo.png'), 'badge' => url('/images/logo.png')],
                'fcm_options' => ['link' => $data['tautan'] ?? url('/notifikasi')],
            ];
        } else {
            $pesan['android'] = ['priority' => 'high', 'notification' => ['channel_id' => 'siakad', 'sound' => 'default']];
        }
        return $pesan;
    }

    /** Token tidak berlaku lagi → dibuang. Galat lain (jaringan, kuota) dibiarkan. */
    private function tokenMati(int $status, array $galat): bool
    {
        $kode = collect($galat['details'] ?? [])->pluck('errorCode')->filter()->all();
        if ($status === 404 || array_intersect($kode, ['UNREGISTERED', 'SENDER_ID_MISMATCH'])) {
            return true;
        }
        return $status === 400 && str_contains(strtolower((string) ($galat['message'] ?? '')), 'registration token');
    }
}
