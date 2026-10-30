<?php

namespace App\Support;

use App\Models\Pengaturan;
use App\Models\Pengumuman;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Gambar pratinjau tautan untuk WhatsApp, Facebook, Instagram (link sticker), X, Telegram, LinkedIn.
 *
 * Aturan yang dipenuhi: 1200×630 (rasio 1,91:1), JPEG < 300 KB (batas WhatsApp), URL absolut.
 * - Berita berfoto  → foto dipotong tengah ke 1200×630.
 * - Berita tanpa foto → kartu bermerek berisi logo, kategori, dan judul berita.
 * - Halaman umum   → kartu bermerek kampus.
 * Hasil disimpan di storage/app/public/bagikan dan dibuat ulang otomatis bila judul/foto berubah.
 */
class GambarBagikan
{
    public const LEBAR = 1200;
    public const TINGGI = 630;
    private const MAKS_BYTE = 300 * 1024;
    private const MAP = 'bagikan';

    /** @return array{url:string, lebar:?int, tinggi:?int} */
    public static function untukPengumuman(Pengumuman $p): array
    {
        $sidik = substr(md5(implode('|', [$p->foto, $p->judul, $p->kategori, optional($p->updated_at)->timestamp, self::sidikMerek()])), 0, 10);
        return self::dapatkan("pengumuman-{$p->id}-", $sidik, function () use ($p) {
            $foto = $p->foto && Storage::disk('public')->exists($p->foto) ? Storage::disk('public')->path($p->foto) : null;
            $gambar = $foto ? self::potongFoto($foto) : null;
            return $gambar ?: self::kartu($p->judul, $p->kategori ?: 'Berita');
        });
    }

    /** Kartu umum kampus untuk beranda & halaman publik lain. */
    public static function situs(?string $judul = null): array
    {
        $sidik = substr(md5(($judul ?? '') . '|' . self::sidikMerek()), 0, 10);
        $awalan = $judul ? 'halaman-' . substr(md5($judul), 0, 8) . '-' : 'situs-';
        return self::dapatkan($awalan, $sidik, fn () => self::kartu($judul, null));
    }

    public static function hapusUntukPengumuman(int $id): void
    {
        foreach (Storage::disk('public')->files(self::MAP) as $f) {
            if (str_starts_with(basename($f), "pengumuman-{$id}-")) {
                Storage::disk('public')->delete($f);
            }
        }
    }

    /** URL absolut; ikut https bila APP_URL https (hosting di balik proxy SSL). */
    public static function absolut(string $url): string
    {
        $url = preg_match('#^https?://#', $url) ? $url : url($url);
        return str_starts_with((string) config('app.url'), 'https://') ? preg_replace('#^http://#', 'https://', $url) : $url;
    }

    // ------------------------------------------------------------------

    private static function dapatkan(string $awalan, string $sidik, callable $buat): array
    {
        $jalur = self::MAP . "/{$awalan}{$sidik}.jpg";
        $disk = Storage::disk('public');
        try {
            if (!$disk->exists($jalur)) {
                $gambar = $buat();
                if (!$gambar) {
                    throw new \RuntimeException('GD tidak dapat membuat gambar.');
                }
                foreach ($disk->files(self::MAP) as $lama) { // versi lama dibuang
                    if (str_starts_with(basename($lama), $awalan)) {
                        $disk->delete($lama);
                    }
                }
                $disk->put($jalur, self::jpeg($gambar));
                imagedestroy($gambar);
            }
            return ['url' => self::absolut(asset('storage/' . $jalur)), 'lebar' => self::LEBAR, 'tinggi' => self::TINGGI];
        } catch (\Throwable $e) {
            Log::warning('Gambar bagikan gagal dibuat: ' . $e->getMessage());
            return ['url' => self::absolut(Pengaturan::logoUrl()), 'lebar' => null, 'tinggi' => null];
        }
    }

    private static function jpeg($gambar): string
    {
        imageinterlace($gambar, true); // progresif: tampil lebih cepat di koneksi lambat
        foreach ([84, 76, 68, 60, 50] as $mutu) {
            ob_start();
            imagejpeg($gambar, null, $mutu);
            $isi = ob_get_clean();
            if (strlen($isi) <= self::MAKS_BYTE) {
                break;
            }
        }
        return $isi;
    }

    private static function muat(string $berkas)
    {
        if (!is_file($berkas) || filesize($berkas) > 20 * 1024 * 1024) {
            return null;
        }
        $g = @imagecreatefromstring((string) file_get_contents($berkas));
        if (!$g) {
            return null;
        }
        // Foto dari HP sering tersimpan miring (EXIF orientation)
        if (function_exists('exif_read_data') && preg_match('/\.jpe?g$/i', $berkas)) {
            $o = (int) (@exif_read_data($berkas)['Orientation'] ?? 1);
            $putar = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($putar && ($baru = imagerotate($g, $putar, 0))) {
                imagedestroy($g);
                $g = $baru;
            }
        }
        return $g;
    }

    private static function potongFoto(string $berkas)
    {
        $src = self::muat($berkas);
        if (!$src) {
            return null;
        }
        [$w, $h] = [imagesx($src), imagesy($src)];
        $skala = max(self::LEBAR / $w, self::TINGGI / $h);
        $cw = (int) round(self::LEBAR / $skala);
        $ch = (int) round(self::TINGGI / $skala);
        // potong di tengah, sedikit ke atas (wajah/judul poster biasanya di sepertiga atas)
        $x = (int) max(0, ($w - $cw) / 2);
        $y = (int) max(0, min($h - $ch, ($h - $ch) * 0.35));

        $dst = imagecreatetruecolor(self::LEBAR, self::TINGGI);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255)); // latar putih untuk PNG transparan
        imagecopyresampled($dst, $src, 0, 0, $x, $y, self::LEBAR, self::TINGGI, $cw, $ch);
        imagedestroy($src);
        return $dst;
    }

    /** Kartu bermerek: warna tema, logo, kategori, judul, nama & domain kampus. */
    private static function kartu(?string $judul, ?string $kategori)
    {
        $W = self::LEBAR;
        $H = self::TINGGI;
        $g = imagecreatetruecolor($W, $H);
        $tema = Pengaturan::themeColors();
        $utama = self::warna($g, $tema['primary'] ?? '#1e293b');
        $aksen = self::warna($g, $tema['accent'] ?? '#334155');
        $putih = imagecolorallocate($g, 255, 255, 255);
        $redup = imagecolorallocatealpha($g, 255, 255, 255, 50);
        $emas = imagecolorallocate($g, 249, 162, 27);

        imagefilledrectangle($g, 0, 0, $W, $H, $utama);
        // ornamen diagonal halus
        for ($i = -$H; $i < $W; $i += 46) {
            imagefilledpolygon($g, [$i, $H, $i + 18, $H, $i + 18 + $H, 0, $i + $H, 0], $aksen);
        }
        imagefilledrectangle($g, 0, 0, $W, $H, imagecolorallocatealpha($g, 0, 0, 0, 92));
        imagefilledrectangle($g, 0, $H - 92, $W, $H, imagecolorallocatealpha($g, 0, 0, 0, 60));
        imagefilledrectangle($g, 0, $H - 92, $W, $H - 86, $emas);

        $logo = self::muat(Pengaturan::logoPhysicalPath());
        $teks = function_exists('imagettftext');
        $tebal = resource_path('fonts/Poppins-Bold.ttf');
        $sedang = resource_path('fonts/Poppins-Medium.ttf');
        $nama = (string) Pengaturan::get('nama_kampus', 'Sekolah Tinggi Theologia GPI Papua');
        $domain = request()->getHost();
        if (in_array($domain, ['', 'localhost', '127.0.0.1'], true)) {
            $domain = parse_url((string) config('app.url'), PHP_URL_HOST) ?: $domain;
        }
        $domain = preg_replace('/^www\./', '', $domain);

        if ($judul && $teks) {
            if ($logo) {
                self::tempel($g, $logo, 70, 70, 120);
            }
            $x = $logo ? 215 : 70;
            imagettftext($g, 20, 0, $x, 112, $emas, $tebal, mb_strtoupper(mb_substr($kategori ?: (string) Pengaturan::get('singkatan_kampus', 'STT GPI Papua'), 0, 40)));
            imagettftext($g, 17, 0, $x, 148, $redup, $sedang, mb_substr($nama, 0, 60));

            [$ukuran, $baris] = self::bungkus($judul, $tebal, $W - 140, [52, 44, 38, 33], 4);
            // GD memakai poin (96 dpi): tinggi huruf ≈ 1,33 × ukuran
            $tinggiBaris = (int) round($ukuran * 1.72);
            $ruangAtas = 185;
            $ruangTinggi = $H - 92 - 25 - $ruangAtas;
            $y = $ruangAtas + (int) max(0, ($ruangTinggi - count($baris) * $tinggiBaris) / 2) + (int) round($ukuran * 1.4);
            foreach ($baris as $b) {
                imagettftext($g, $ukuran, 0, 70, $y, $putih, $tebal, $b);
                $y += $tinggiBaris;
            }
        } elseif ($logo) {
            self::tempel($g, $logo, (int) ($W / 2 - 115), $teks ? 90 : 160, 230);
            if ($teks) {
                [$ukuran, $baris] = self::bungkus($judul ?: $nama, $tebal, $W - 160, [44, 38, 32], 2);
                $y = 365 + (int) round($ukuran * 1.33);
                foreach ($baris as $b) {
                    $kotak = imagettfbbox($ukuran, 0, $tebal, $b);
                    imagettftext($g, $ukuran, 0, (int) (($W - ($kotak[2] - $kotak[0])) / 2), $y, $putih, $tebal, $b);
                    $y += (int) round($ukuran * 1.72);
                }
            }
        }

        if ($teks) {
            imagettftext($g, 20, 0, 70, $H - 34, $putih, $sedang, (string) Pengaturan::get('singkatan_kampus', 'STT GPI Papua'));
            $kotak = imagettfbbox(20, 0, $sedang, $domain);
            imagettftext($g, 20, 0, $W - 70 - ($kotak[2] - $kotak[0]), $H - 34, $redup, $sedang, $domain);
        }
        if ($logo) {
            imagedestroy($logo);
        }
        return $g;
    }

    /** Pilih ukuran huruf terbesar yang muat; potong dengan "…" bila tetap terlalu panjang. */
    private static function bungkus(string $teks, string $font, int $lebar, array $ukuran, int $maksBaris): array
    {
        $kata = preg_split('/\s+/u', trim(strip_tags($teks))) ?: [''];
        foreach ($ukuran as $i => $u) {
            $baris = [];
            $kini = '';
            foreach ($kata as $k) {
                $coba = $kini === '' ? $k : "{$kini} {$k}";
                $kotak = imagettfbbox($u, 0, $font, $coba);
                if ($kotak[2] - $kotak[0] > $lebar && $kini !== '') {
                    $baris[] = $kini;
                    $kini = $k;
                } else {
                    $kini = $coba;
                }
            }
            $baris[] = $kini;
            if (count($baris) <= $maksBaris || $i === count($ukuran) - 1) {
                if (count($baris) > $maksBaris) {
                    $baris = array_slice($baris, 0, $maksBaris);
                    $baris[$maksBaris - 1] = rtrim(mb_substr($baris[$maksBaris - 1], 0, max(1, mb_strlen($baris[$maksBaris - 1]) - 2)), ' ,.;:') . '…';
                }
                return [$u, $baris];
            }
        }
        return [end($ukuran), [$teks]];
    }

    private static function tempel($g, $logo, int $x, int $y, int $ukuran): void
    {
        [$w, $h] = [imagesx($logo), imagesy($logo)];
        $s = $ukuran / max($w, $h);
        imagealphablending($g, true);
        imagecopyresampled($g, $logo, $x + (int) (($ukuran - $w * $s) / 2), $y + (int) (($ukuran - $h * $s) / 2), 0, 0, (int) ($w * $s), (int) ($h * $s), $w, $h);
    }

    private static function warna($g, string $hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $n = hexdec(str_pad(substr($hex, 0, 6), 6, '0'));
        return imagecolorallocate($g, ($n >> 16) & 255, ($n >> 8) & 255, $n & 255);
    }

    /** Kartu dibuat ulang bila logo / nama / warna tema kampus berubah. */
    private static function sidikMerek(): string
    {
        $logo = Pengaturan::logoPhysicalPath();
        return md5(implode('|', [Pengaturan::get('nama_kampus'), Pengaturan::get('singkatan_kampus'), json_encode(Pengaturan::themeColors()), $logo, @filemtime($logo)]));
    }
}
