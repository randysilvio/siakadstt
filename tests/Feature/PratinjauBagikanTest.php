<?php

use App\Models\Pengumuman;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('halaman berita memuat meta pratinjau media sosial dengan gambar 1200x630', function () {
    Storage::fake('public');
    $foto = UploadedFile::fake()->image('wisuda.jpg', 1600, 2000)->store('pengumuman', 'public');
    $p = Pengumuman::create(['judul' => 'Wisuda XII', 'slug' => 'wisuda-xii', 'kategori' => 'Kegiatan', 'foto' => $foto,
        'konten' => '<p>Wisuda angkatan <b>XII</b> berlangsung meriah.</p>', 'target_role' => 'semua']);

    $res = $this->get('/wisuda-xii')->assertOk()
        ->assertSee('property="og:title" content="Wisuda XII"', false)
        ->assertSee('property="og:type" content="article"', false)
        ->assertSee('content="Wisuda angkatan XII berlangsung meriah."', false)
        ->assertSee('name="twitter:card" content="summary_large_image"', false)
        ->assertSee('wa.me/?text=', false);

    preg_match('/property="og:image" content="([^"]+)"/', $res->getContent(), $m);
    expect($m[1] ?? '')->toContain('/storage/bagikan/pengumuman-' . $p->id . '-');
    $berkas = 'bagikan/' . basename(parse_url($m[1], PHP_URL_PATH));
    Storage::disk('public')->assertExists($berkas);
    [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($berkas));
    expect([$w, $h])->toBe([1200, 630])->and(strlen(Storage::disk('public')->get($berkas)))->toBeLessThan(300 * 1024);

    // Judul berubah → gambar baru, yang lama dibuang
    $p->update(['judul' => 'Wisuda XII 2026']);
    $this->get('/wisuda-xii')->assertOk();
    Storage::disk('public')->assertMissing($berkas);
    expect(collect(Storage::disk('public')->files('bagikan'))->filter(fn ($f) => str_contains($f, "pengumuman-{$p->id}-")))->toHaveCount(1);

    $p->delete();
    expect(collect(Storage::disk('public')->files('bagikan'))->filter(fn ($f) => str_contains($f, "pengumuman-{$p->id}-")))->toHaveCount(0);
});

test('berita tanpa foto memakai kartu bermerek, beranda dan daftar berita punya pratinjau', function () {
    Storage::fake('public');
    Pengumuman::create(['judul' => 'PMB Gelombang II Dibuka', 'slug' => 'pmb-2', 'konten' => 'Daftar sekarang', 'target_role' => 'semua']);
    $this->get('/pmb-2')->assertOk()->assertSee('/storage/bagikan/pengumuman-', false);
    $this->get('/berita')->assertOk()->assertSee('property="og:image"', false);
    $this->get('/')->assertOk()->assertSee('property="og:image"', false);
});
