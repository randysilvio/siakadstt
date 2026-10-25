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
            'nama_kampus'             => 'Sekolah Tinggi Teologi Gereja Protestan Indonesia Papua',
            'singkatan_kampus'        => 'STT GPI PAPUA',
            'bentuk_institusi'        => 'Sekolah Tinggi', // Sekolah Tinggi | Institut | Universitas | Akademi
            'sebutan_pimpinan'        => 'Ketua',          // Ketua | Rektor | Direktur
            'nama_rektor'             => 'Wensly Peniel Raprap, S.Th., M.Pd',
            'nidn_rektor'             => '8935756657130152',
            'label_id_rektor'         => 'NUPTK',          // NIDN | NUPTK | NIP
            'kode_pt_pddikti'         => '143045',
            'akreditasi_institusi'    => 'Baik',
            'no_sk_akreditasi'        => 'BAN-PT No. 234/SK/BAN-PT/Ak/PT/2024',
            'kota_domisili'           => 'Fakfak',

            // TAB 2: STRUKTUR BADAN PENYELENGGARA (SINODE/YAYASAN), SENAT, & PEJABAT KAMPUS
            'nama_badan_penyelenggara'=> 'Badan Pekerja Sinode Gereja Protestan Indonesia (GPI) di Papua',
            'jenis_badan_penyelenggara'=> 'Sinode / Yayasan Penyelenggara',
            'ketua_yayasan'           => 'Pdt. Donald E.M.M. Salima, S.Th., M.Si',
            'sekretaris_yayasan'      => 'Pdt. Izak Samuel Deda, M.Th',
            'bendahara_yayasan'       => 'Pnt. Drs. H.B. Wambrauw',
            'ketua_senat'             => 'Dr. Janse Belandina Non-Serrano, M.Th',
            'sekretaris_senat'        => 'Pdt. FredrikPadang, M.Th',
            'wakil_pimpinan_1'        => 'Wakil Ketua I (Bidang Akademik)',
            'nama_wakil_1'            => 'Pdt. Dr. Sostenes Sumihe, M.Th',
            'wakil_pimpinan_2'        => 'Wakil Ketua II (Bidang Administrasi & Keuangan)',
            'nama_wakil_2'            => 'Pdt. Marlin Tampake, M.Th',
            'wakil_pimpinan_3'        => 'Wakil Ketua III (Bidang Kemahasiswaan & Alumni)',
            'nama_wakil_3'            => 'Pdt. Yohanis Hermanus, M.Th',
            'kepala_upm'              => 'Unit Penjaminan Mutu (UPM)',
            'nama_kepala_upm'         => 'Dr. HendraPutra, M.Pd.K',
            'kepala_lppm'             => 'Lembaga Penelitian & PkM (LPPM)',
            'nama_kepala_lppm'        => 'Pdt. Lukas Kareth, M.Th',
            'nama_kepala_baak'        => 'Biro Administrasi Akademik & Kemahasiswaan (BAAK)',
            'nama_kepala_perpus'      => 'Unit Pelaksana Teknis Perpustakaan',
            'bagan_struktur_path'     => '',

            // TAB 3: LOGO, VISUAL & KOP SURAT PDF
            'logo_path'               => '',
            'favicon_path'            => '',
            'foto_pimpinan_path'      => '',
            'kop_baris_1'             => 'GEREJA PROTESTAN INDONESIA DI PAPUA',
            'kop_baris_2'             => 'SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA',
            'kop_baris_3'             => 'TERAKREDITASI BAN-PT',
            'kop_alamat'              => 'Jl. Jend. Sudirman, Kotak Pos 116, Fakfak 98611 - Provinsi Papua Barat | Email: info@sttgpipapua.ac.id',

            // TAB 4: THEME ENGINE (WARNA & GAYA UI)
            'theme_preset'            => 'monochrome', // monochrome | royal_blue | emerald_teal | crimson_maroon | imperial_purple | custom
            'theme_primary_color'     => '#212529',
            'theme_accent_color'      => '#0d9488',
            'theme_border_radius'     => '0px',        // 0px (Square) | 8px (Rounded)

            // TAB 5: KONTEN PORTAL PUBLIK (GAYA UKSW)
            'motto_kampus'            => 'Membentuk Pelayan dan Pendidik Kristen yang Unggul, Berkarakter Kristus, dan Kontekstual di Tanah Papua.',
            'judul_sambutan'          => 'Sambutan Pimpinan Institusi',
            'isi_sambutan'            => 'Selamat datang di portal resmi Sekolah Tinggi Teologi Gereja Protestan Indonesia (STT GPI) Papua. Sebagai perguruan tinggi teologi dan pendidikan agama Kristen di Tanah Papua, kami berkomitmen menyelenggarakan pendidikan tinggi berbasis Outcome-Based Education (OBE) yang mengintegrasikan keunggulan akademik, spiritualitas pelayanan, dan pengabdian kontekstual bagi gereja serta masyarakat.',
            'sejarah_kampus'          => 'Sekolah Tinggi Teologi Gereja Protestan Indonesia (STT GPI) Papua didirikan untuk menjawab kebutuhan tenaga pelayan gerejawi dan pendidik Kristen yang handal di wilayah pelayanan GPI Papua dan Indonesia Timur. Sejak berdiri, STT GPI Papua terus berkembang dalam mutu akademik, riset teologi kontekstual, dan pemberdayaan warga jemaat.',
            'visi_kampus'             => 'Menjadi Perguruan Tinggi Teologi dan Pendidikan Kristen yang Unggul, Inklusif, dan Kontekstual dalam Mewujudkan Transformasi Gereja dan Masyarakat di Tanah Papua.',
            'misi_kampus'             => "Menyelenggarakan pendidikan dan pengajaran teologi serta pendidikan agama Kristen yang bermutu tinggi berbasis kurikulum OBE.\nMelaksanakan penelitian ilmiah dan kajian teologi kontekstual yang relevan dengan tantangan gereja dan masyarakat di Tanah Papua.\nMelaksanakan pengabdian kepada masyarakat dan pelayanan gerejawi yang transformatif.\nMengembangkan tata kelola perguruan tinggi yang transparan, akuntabel, dan berbasis teknologi informasi modern.",
            'video_profil_url'        => '',

            // TAB 6: AKADEMIK, SAKLAR SKALA UNIVERSITAS & KAMUS ISTILAH
            'status_krs'              => 'buka',       // buka | tutup
            'fitur_fakultas_aktif'    => '0',          // 0 = Sekolah Tinggi (Langsung Prodi), 1 = Universitas (Aktifkan Fakultas)
            'fitur_kelas_paralel'     => '1',          // 1 = Aktifkan Rombel Kelas (A/B/C)
            'istilah_pembimbing'      => 'Dosen Tutor',// Dosen Tutor | Dosen Wali | Pembimbing Akademik
            'istilah_perwalian'       => 'Tutorship',  // Tutorship | Perwalian
            'bobot_hadir_default'     => '10',
            'bobot_tugas_default'     => '20',
            'bobot_uts_default'       => '30',
            'bobot_uas_default'       => '40',

            // TAB 7: KEUANGAN, REKENING PEMBAYARAN, KONTAK & MEDSOS
            'nama_bank_kampus'        => 'Bank Papua / BRI',
            'no_rekening_kampus'      => '100-02-0012345-6',
            'atas_nama_rekening'      => 'STT GPI PAPUA',
            'alamat_kampus'           => 'Jl. Jenderal Sudirman, Distrik Fakfak, Kabupaten Fakfak, Provinsi Papua Barat 98611',
            'telepon_kampus'          => '(0956) 22114',
            'whatsapp_admisi'         => '6281234567890',
            'email_kampus'            => 'info@sttgpipapua.ac.id',
            'maps_embed_url'          => '',
            'facebook_url'            => '',
            'instagram_url'           => '',
            'youtube_url'             => '',
            'tiktok_url'              => '',
        ];
    }

    /**
     * Mengambil semua pengaturan yang digabung dengan nilai default dan disimpan di Cache.
     */
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

    /**
     * Mengambil 1 nilai pengaturan secara cepat dari Cache.
     */
    public static function get(string $key, $default = null)
    {
        $all = self::allCached();
        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }
        $defaults = self::defaults();
        return $default ?? ($defaults[$key] ?? null);
    }

    /**
     * Menyimpan / memperbarui 1 pengaturan dan membersihkan Cache.
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_null($value) ? '' : (string) $value]
        );
        self::clearCache();
    }

    /**
     * Mengambil URL Logo Utama Kampus (mendukung file upload maupun fallback default).
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
     * Mengambil Path Fisik Logo Utama Kampus untuk DomPDF.
     */
    public static function logoPhysicalPath(): string
    {
        $path = self::get('logo_path', '');
        if (!empty($path) && file_exists(storage_path('app/public/' . $path))) {
            return storage_path('app/public/' . $path);
        }
        return public_path('images/logo.png');
    }

    /**
     * Mengambil warna tema aktif (Primary & Accent) berdasarkan preset yang dipilih.
     */
    public static function themeColors(): array
    {
        $preset = self::get('theme_preset', 'monochrome');
        $presets = [
            'monochrome'      => ['primary' => '#212529', 'accent' => '#0d9488', 'navbar_bg' => '#ffffff', 'navbar_text' => '#212529'],
            'royal_blue'      => ['primary' => '#0f2942', 'accent' => '#d97706', 'navbar_bg' => '#ffffff', 'navbar_text' => '#0f2942'],
            'emerald_teal'    => ['primary' => '#064e3b', 'accent' => '#0d9488', 'navbar_bg' => '#ffffff', 'navbar_text' => '#064e3b'],
            'crimson_maroon'  => ['primary' => '#7f1d1d', 'accent' => '#b45309', 'navbar_bg' => '#ffffff', 'navbar_text' => '#7f1d1d'],
            'imperial_purple' => ['primary' => '#3b0764', 'accent' => '#ca8a04', 'navbar_bg' => '#ffffff', 'navbar_text' => '#3b0764'],
        ];

        if ($preset === 'custom') {
            $customPrimary = self::get('theme_primary_color', '#212529');
            $customAccent  = self::get('theme_accent_color', '#0d9488');
            return [
                'primary'     => $customPrimary,
                'accent'      => $customAccent,
                'navbar_bg'   => '#ffffff',
                'navbar_text' => $customPrimary,
                'radius'      => self::get('theme_border_radius', '0px'),
            ];
        }

        $chosen = $presets[$preset] ?? $presets['monochrome'];
        $chosen['radius'] = self::get('theme_border_radius', '0px');
        return $chosen;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}