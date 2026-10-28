<?php

use Illuminate\Support\Facades\Route;
use Tests\Concerns\SeedLengkap;

uses(SeedLengkap::class);

/**
 * Uji asap: membuka setiap rute GET dengan setiap peran. Hasil lengkap ditulis ke JELAJAH_OUT (bila diset).
 * Gagal bila ada respons 500 / exception.
 */
test('semua halaman GET dapat dibuka tanpa error server oleh setiap peran', function () {
    $s = $this->seedLengkap();
    $notifId = $s['dUser']->notifications()->first()->id;

    $param = [
        'programStudi' => $s['prodi']->id, 'program_studi' => $s['prodi']->id, 'pengumuman' => $s['berita']->slug,
        'dosen' => $s['dosen']->id, 'token' => $s['terbit']->kode, 'camaba' => $s['camaba']->id, 'mahasiswa' => $s['mhs']->id,
        'mataKuliah' => $s['mk']->id, 'mata_kuliah' => $s['mk']->id, 'kurikulum' => $s['mk']->kurikulum_id, 'tahun_akademik' => $s['ta']->id,
        'kalender' => $s['kegiatan']->id, 'user' => $s['mUser']->id, 'tendik' => \App\Models\Tendik::first()->id, 'lokasi' => $s['lokasi']->id,
        'sesi' => $s['sesi']->id, 'evaluasi_sesi' => $s['sesi']->id, 'evaluasi_pertanyaan' => $s['tanya']->id, 'slideshow' => $s['slide']->id,
        'dokumen_publik' => $s['dokPublik']->id, 'surat_keputusan' => $s['sk']->id, 'pmb_period' => $s['periode']->id, 'verum_kela' => $s['kelas']->id,
        'verum_tuga' => $s['tugas']->id, 'bimbingan' => $s['bimbingan']->id, 'pengajuanSurat' => $s['surat']->id, 'koleksi' => $s['koleksi']->id,
        'id' => $notifId, 'kategori' => 'mahasiswa', 'jenis' => 'bukti-bayar',
    ];
    $override = [
        'dosen.public.show' => ['dosen' => '111'],
        'admin.evaluasi-hasil.show' => ['dosen' => $s['dosen']->id], 'admin.evaluasi-hasil.cetak' => ['dosen' => $s['dosen']->id],
        'pengumuman.internal.show' => ['pengumuman' => $s['internal']->slug],
        'berkas.unduh' => ['id' => $s['spp']->id],
    ];
    $lewati = ['ignition.healthCheck', 'sanctum.csrf-cookie', 'verification.verify', 'password.reset', 'admin.feeder.backup-sql'];

    $aktor = [
        'tamu' => null, 'admin' => $s['admin'], 'admin_umum' => $s['adminUmum'], 'dosen' => $s['dUser'], 'kaprodi' => $s['kUser'],
        'dosen_keu' => $s['keuDosenUser'], 'keuangan' => $s['keuangan'], 'pustakawan' => $s['pustakawan'], 'rektorat' => $s['rektorat'],
        'mutu' => $s['mutu'], 'mahasiswa' => $s['mUser'], 'mahasiswa2' => $s['m2User'], 'camaba' => $s['camabaUser'], 'panitia' => $s['panitia'],
    ];

    $hasil = [];
    $error = [];
    foreach (Route::getRoutes() as $route) {
        if (!in_array('GET', $route->methods()) || in_array($route->getName(), $lewati, true) || !$route->getName() && !str_starts_with($route->uri(), 'api/')) {
            continue;
        }
        $nilai = array_merge($param, $override[$route->getName()] ?? []);
        $uri = preg_replace_callback('/\{(\w+)\??\}/', fn ($m) => $nilai[$m[1]] ?? ($m[1] === 'sisi' ? '' : 'X' . $m[1]), $route->uri());
        $uri = '/' . trim(preg_replace('#/+#', '/', $uri), '/');

        foreach ($aktor as $nama => $user) {
            app('auth')->forgetGuards();
            $req = $user ? $this->actingAs($user) : $this;
            $res = str_starts_with($uri, '/api/') ? $req->getJson($uri) : $req->get($uri);
            $status = $res->getStatusCode();
            $info = $status;
            if ($res->isRedirect()) {
                $loc = parse_url($res->headers->get('Location'), PHP_URL_PATH) ?: '/';
                $info = '302>' . $loc;
            }
            $hasil[$route->getName() ?: $uri][$nama] = $info;
            if ($status >= 500) {
                $e = $res->exception;
                $error[] = sprintf('%s [%s] %s: %s @ %s:%s', $uri, $nama, $e ? get_class($e) : '', $e?->getMessage(), $e ? str_replace(base_path() . '/', '', $e->getFile()) : '', $e?->getLine());
            }
        }
    }

    if ($out = env('JELAJAH_OUT')) {
        file_put_contents($out, json_encode(['hasil' => $hasil, 'error' => $error], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    expect($error)->toBe([]);
})->group('asap');
