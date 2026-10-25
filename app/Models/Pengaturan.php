<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturans';

    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'siakad_global_pengaturans_cache';

    /**
     * Nilai default seluruh pengaturan skala tertinggi (White-Label & Enterprise).
     */
    public static function defaults(): array
    {
        return [
            // TAB 1: IDENTITAS & PIMPINAN UTAMA
            'nama_kampus'              => 'Sekolah Tinggi Teologi Gereja Protestan Indonesia Papua',
            'singkatan_kampus'         => 'STT GPI PAPUA',
            'bentuk_institusi'         => 'Sekolah Tinggi',
            'sebutan_pimpinan'         => 'Ketua',
            'nama_rektor'              => 'Wensly Peniel Raprap, S.Th., M.Pd',
            'nidn_rektor'              => '8935756657130152',
            'label_id_rektor'          => 'NUPTK',
            'kode_pt_pddikti'          => '143045',
            'akreditasi_institusi'     => 'Baik',
            'no_sk_akreditasi'         => 'BAN-PT No. 234/SK/BAN-PT/Ak/PT/2024',
            'kota_domisili'            => 'Fakfak',

            // TAB 2: STRUKTUR BADAN PENYELENGGARA (SINODE/YAYASAN), SENAT, & PEJABAT KAMPUS
            'nama_badan_penyelenggara' => 'Badan Pekerja Sinode Gereja Protestan Indonesia (GPI) di Papua',
            'jenis_badan_penyelenggara'=> 'Sinode / Yayasan Penyelenggara',
            'ketua_yayasan'            => 'Pdt. Donald E.M.M. Salima, S.Th., M.Si',
            'sekretaris_yayasan'       => 'Pdt. Izak Samuel Deda, M.Th',
            'bendahara_yayasan'        => 'Pnt. Drs. H.B. Wambrauw',
            'ketua_senat'              => 'Dr. Janse Belandina Non-Serrano, M.Th',
            'sekretaris_senat'         => 'Pdt. Fredrik Padang, M.Th',
            'wakil_pimpinan_1'         => 'Wakil Ketua I (Bidang Akademik)',
            'nama_wakil_1'             => 'Pdt. Dr. Sostenes Sumihe, M.Th',
            'wakil_pimpinan_2'         => 'Wakil Ketua II (Bidang Administrasi & Keuangan)',
            'nama_wakil_2'             => 'Pdt. Marlin Tampake, M.Th',
            'wakil_pimpinan_3'         => 'Wakil Ketua III (Bidang Kemahasiswaan & Alumni)',
            'nama_wakil_3'             => 'Pdt. Yohanis Hermanus, M.Th',
            'kepala_upm'               => 'Unit Penjaminan Mutu (UPM)',
            'nama_kepala_upm'          => 'Dr. Hendra Putra, M.Pd.K',
            'kepala_lppm'              => 'Lembaga Penelitian & PkM (LPPM)',
            'nama_kepala_lppm'         => 'Pdt. Lukas Kareth, M.Th',
            'nama_kepala_baak'         => 'Biro Administrasi Akademik & Kemahasiswaan (BAAK)',
            'nama_kepala_perpus'       => 'Unit Pelaksana Teknis Perpustakaan',
            'bagan_struktur_path'      => '',

            // TAB 3: MULTI-LOGO, LOGO TEMATIK/EVENT (DIES NATALIS), WATERMARK BATIK PAPUA, & KOP PDF
            'logo_path'                => '',
            'logo_event_path'          => '', // Logo khusus tema kegiatan publik (Dies Natalis, Wisuda, dll.)
            'label_event_publik'       => '', // Contoh: Dies Natalis ke-25 STT GPI Papua
            'favicon_path'             => '',
            'foto_pimpinan_path'       => '',
            'watermark_card_path'      => '', // Ikon/Batik Papua untuk Focal Point Watermark
            'watermark_section_path'   => '', // Ornamen Batik Papua untuk Banner & Footer
            'watermark_opacity'        => '0.06',
            'kop_baris_1'              => 'GEREJA PROTESTAN INDONESIA DI PAPUA',
            'kop_baris_2'              => 'SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA',
            'kop_baris_3'              => 'TERAKREDITASI BAN-PT',
            'kop_alamat'               => 'Jl. Jend. Sudirman, Kotak Pos 116, Fakfak 98611 - Provinsi Papua Barat | Email: info@sttgpipapua.ac.id',

            // TAB 4: THEME ENGINE (WARNA & GAYA UI)
            'theme_preset'             => 'monochrome',
            'theme_primary_color'      => '#1e293b',
            'theme_accent_color'       => '#334155',
            'theme_border_radius'      => '10px',

            // TAB 5: KONTEN PORTAL PUBLIK
            'motto_kampus'             => 'Membentuk Pelayan dan Pendidik Kristen yang Berintegritas, Berkarakter Kristus, dan Kontekstual di Tanah Papua.',
            'judul_sambutan'           => 'Sambutan Pimpinan Institusi',
            'isi_sambutan'             => 'Selamat datang di portal resmi Sekolah Tinggi Teologi Gereja Protestan Indonesia (STT GPI) Papua. Sebagai perguruan tinggi teologi dan pendidikan agama Kristen di Tanah Papua, kami berkomitmen menyelenggarakan pendidikan tinggi yang mengintegrasikan keunggulan akademik, spiritualitas pelayanan, dan pengabdian kontekstual bagi gereja serta masyarakat.',
            'sejarah_kampus'           => 'Sekolah Tinggi Teologi Gereja Protestan Indonesia (STT GPI) Papua didirikan untuk menjawab kebutuhan tenaga pelayan gerejawi dan pendidik Kristen yang handal di wilayah pelayanan GPI Papua dan Indonesia Timur. Sejak berdiri, STT GPI Papua terus berkembang dalam mutu akademik, riset teologi kontekstual, dan pemberdayaan warga jemaat.',
            'visi_kampus'              => 'Menjadi Perguruan Tinggi Teologi dan Pendidikan Kristen yang Unggul, Inklusif, dan Kontekstual dalam Mewujudkan Transformasi Gereja dan Masyarakat di Tanah Papua.',
            'misi_kampus'              => "Menyelenggarakan pendidikan dan pengajaran teologi serta pendidikan agama Kristen yang bermutu tinggi.\nMelaksanakan penelitian ilmiah dan kajian teologi kontekstual yang relevan dengan tantangan gereja dan masyarakat di Tanah Papua.\nMelaksanakan pengabdian kepada masyarakat dan pelayanan gerejawi yang transformatif.\nMengembangkan tata kelola perguruan tinggi yang transparan, akuntabel, dan berbasis teknologi informasi.",
            'video_profil_url'         => '',

            // TAB 6: AKADEMIK, SAKLAR SKALA UNIVERSITAS & KAMUS ISTILAH
            'status_krs'               => 'buka',
            'fitur_fakultas_aktif'     => '0',
            'fitur_kelas_paralel'      => '1',
            'istilah_pembimbing'       => 'Dosen Tutor',
            'istilah_perwalian'        => 'Tutorship',
            'bobot_hadir_default'      => '10',
            'bobot_tugas_default'      => '20',
            'bobot_uts_default'        => '30',
            'bobot_uas_default'        => '40',

            // TAB 7: KEUANGAN, REKENING PEMBAYARAN, KONTAK & MEDSOS
            'nama_bank_kampus'         => 'Bank Papua / BRI',
            'no_rekening_kampus'       => '100-02-0012345-6',
            'atas_nama_rekening'      => 'STT GPI PAPUA',
            'alamat_kampus'            => 'Jl. Jenderal Sudirman, Distrik Fakfak, Kabupaten Fakfak, Provinsi Papua Barat 98611',
            'telepon_kampus'           => '(0956) 22114',
            'whatsapp_admisi'          => '6281234567890',
            'email_kampus'             => 'info@sttgpipapua.ac.id',
            'maps_embed_url'           => '',
            'facebook_url'             => '',
            'instagram_url'            => '',
            'youtube_url'              => '',
            'tiktok_url'               => '',
        ];
    }

    public static function allCached(): array
    {
        $defaults = self::defaults();

        try {
            $dbSettings = Cache::remember(self::CACHE_KEY, 3600, function () {
                if (!Schema::hasTable('pengaturans')) {
                    return [];
                }
                return self::query()->pluck('value', 'key')->toArray();
            });
        } catch (\Throwable $e) {
            $dbSettings = [];
        }

        $merged = $defaults;
        foreach ($dbSettings as $k => $v) {
            if ($v !== null && $v !== '') {
                $merged[$k] = $v;
            } elseif (array_key_exists($k, $dbSettings) && in_array($k, ['fitur_fakultas_aktif', 'fitur_kelas_paralel'])) {
                $merged[$k] = (string) $v;
            }
        }

        return $merged;
    }

    public static function get(string $key, $default = null)
    {
        $all = self::allCached();
        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }
        $defaults = self::defaults();
        return $default ?? ($defaults[$key] ?? null);
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_null($value) ? '' : (string) $value]
        );
        self::clearCache();
    }

    /**
     * URL Logo Inti Institusi (Untuk SIAKAD Internal, Kop PDF, & Brand Utama).
     */
    public static function logoUrl(): string
    {
        $path = self::get('logo_path', '');
        if (!empty($path) && file_exists(storage_path('app/public/' . $path))) {
            return asset('storage/' . $path);
        }
        return asset('images/logo.png');
    }

    /**
     * URL Logo Event / Tematik Halaman Publik (Misal: Dies Natalis, Bulan Teologi, Wisuda).
     * Mengembalikan null jika sedang tidak ada event khusus yang dipasang.
     */
    public static function eventLogoUrl(): ?string
    {
        $path = self::get('logo_event_path', '');
        if (!empty($path) && file_exists(storage_path('app/public/' . $path))) {
            return asset('storage/' . $path);
        }
        return null;
    }

    /**
     * URL Watermark Fokus Eksklusif (Sambutan Pimpinan & Kartu Highlight Utama).
     */
    public static function cardWatermarkUrl(): string
    {
        $path = self::get('watermark_card_path', '');
        if (!empty($path) && file_exists(storage_path('app/public/' . $path))) {
            return asset('storage/' . $path);
        }
        return self::logoUrl();
    }

    /**
     * URL Ornamen Batik Papua untuk Watermark Banner Header & Footer.
     */
    public static function sectionWatermarkUrl(): string
    {
        $path = self::get('watermark_section_path', '');
        if (!empty($path) && file_exists(storage_path('app/public/' . $path))) {
            return asset('storage/' . $path);
        }
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" fill="none" stroke="#ffffff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M60 8 L112 60 L60 112 L8 60 Z"/><path d="M60 24 L96 60 L60 96 L24 60 Z"/><circle cx="60" cy="60" r="14"/><path d="M32 32 C42 22, 52 32, 60 24 C68 32, 78 22, 88 32"/><path d="M32 88 C42 98, 52 88, 60 96 C68 88, 78 98, 88 88"/></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public static function logoPhysicalPath(): string
    {
        $path = self::get('logo_path', '');
        if (!empty($path) && file_exists(storage_path('app/public/' . $path))) {
            return storage_path('app/public/' . $path);
        }
        return public_path('images/logo.png');
    }

    public static function themeColors(): array
    {
        $preset = self::get('theme_preset', 'monochrome');
        $presets = [
            'monochrome'      => ['primary' => '#1e293b', 'accent' => '#334155', 'navbar_bg' => '#ffffff', 'navbar_text' => '#1e293b'],
            'royal_blue'      => ['primary' => '#0f2942', 'accent' => '#1e3a8a', 'navbar_bg' => '#ffffff', 'navbar_text' => '#0f2942'],
            'emerald_teal'    => ['primary' => '#064e3b', 'accent' => '#115e59', 'navbar_bg' => '#ffffff', 'navbar_text' => '#064e3b'],
            'crimson_maroon'  => ['primary' => '#7f1d1d', 'accent' => '#991b1b', 'navbar_bg' => '#ffffff', 'navbar_text' => '#7f1d1d'],
            'imperial_purple' => ['primary' => '#3b0764', 'accent' => '#581c87', 'navbar_bg' => '#ffffff', 'navbar_text' => '#3b0764'],
        ];

        $radius = self::get('theme_border_radius', '10px');
        $wmOpacity = self::get('watermark_opacity', '0.06');

        if ($preset === 'custom') {
            $customPrimary = self::get('theme_primary_color', '#1e293b');
            $customAccent  = self::get('theme_accent_color', '#334155');
            return [
                'primary'           => $customPrimary,
                'accent'            => $customAccent,
                'navbar_bg'         => '#ffffff',
                'navbar_text'       => $customPrimary,
                'radius'            => $radius,
                'watermark_opacity' => $wmOpacity,
            ];
        }

        $chosen = $presets[$preset] ?? $presets['monochrome'];
        $chosen['radius'] = $radius;
        $chosen['watermark_opacity'] = $wmOpacity;
        return $chosen;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}