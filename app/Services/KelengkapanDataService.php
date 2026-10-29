<?php

namespace App\Services;

use App\Http\Controllers\Admin\FeederExportController;
use App\Models\Dosen;
use App\Models\EvaluasiSesi;
use App\Models\Jadwal;
use App\Models\KrsPersetujuan;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Pembayaran;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Pengingat kelengkapan & kesehatan data untuk Admin SIAKAD (hanya pengingat; tidak mengubah hak akses).
 * Setiap butir menyebut BIDANG yang perlu menindaklanjuti agar Admin dapat meneruskannya, dan dikirim
 * sebagai notifikasi mingguan ke bidang tersebut (Keuangan, Kaprodi, Penjaminan Mutu).
 *
 * Tingkat: kritis (proses macet / laporan resmi salah), penting (harus beres sebelum tahap tertentu), saran.
 */
class KelengkapanDataService
{
    public const CACHE = 'siakad-kelengkapan-data';
    public const URUTAN = ['kritis' => 0, 'penting' => 1, 'saran' => 2];

    private array $butir = [];

    public function ringkasan(bool $segarkan = false): array
    {
        if ($segarkan) {
            Cache::forget(self::CACHE);
        }
        return Cache::remember(self::CACHE, now()->addMinutes(10), fn () => $this->periksa());
    }

    /** @return array{butir: array, jumlah: array, diperiksa: string, agenda: ?string} */
    public function periksa(): array
    {
        $this->butir = [];
        $ta = TahunAkademik::where('is_active', true)->get();
        $aktif = $ta->first();

        // Agenda pengendali terdekat (untuk keterangan tenggat)
        $krs = $aktif ? KalenderPengendali::acara('krs', $aktif->id) : null;
        $tenggatKrs = null;
        if ($krs && now()->startOfDay()->lte(Carbon::parse($krs->tanggal_mulai))) {
            $hari = now()->startOfDay()->diffInDays(Carbon::parse($krs->tanggal_mulai)->startOfDay());
            $tenggatKrs = 'KRS dibuka ' . Carbon::parse($krs->tanggal_mulai)->translatedFormat('d M Y') . ($hari <= 14 ? " ({$hari} hari lagi)" : '');
        }

        $this->akademik($ta, $aktif);
        if ($aktif) {
            $this->perkuliahan($aktif, $tenggatKrs);
            $this->krsDanNilai($aktif);
            $this->keuangan($aktif, $tenggatKrs);
        }
        $this->mahasiswa($tenggatKrs);
        $this->dosen();
        $this->sistem();

        $butir = collect($this->butir)->sortBy(fn ($b) => [self::URUTAN[$b['tingkat']], $b['bidang']])->values()->all();

        return [
            'butir'     => $butir,
            'jumlah'    => collect(self::URUTAN)->map(fn ($_, $t) => collect($butir)->where('tingkat', $t)->count())->all(),
            'diperiksa' => now()->toDateTimeString(),
            'agenda'    => $tenggatKrs,
        ];
    }

    // ------------------------------------------------------------------ pembantu

    private function tambah(string $kunci, string $tingkat, string $bidang, string $judul, int $jumlah, string $alasan, array $opsi = []): void
    {
        if ($jumlah <= 0) {
            return;
        }
        $this->butir[] = [
            'kunci'   => $kunci,
            'tingkat' => $tingkat,
            'bidang'  => $bidang,
            'tujuan'  => $opsi['tujuan'] ?? null,     // peran penerima notifikasi mingguan: keuangan | penjaminan_mutu | kaprodi:{prodi_id}
            'judul'   => $judul,
            'jumlah'  => $jumlah,
            'contoh'  => array_values(array_slice($opsi['contoh'] ?? [], 0, 6)),
            'alasan'  => $alasan,
            'tenggat' => $opsi['tenggat'] ?? null,
            'tautan'  => isset($opsi['rute']) && Route::has($opsi['rute']) ? route($opsi['rute'], $opsi['param'] ?? []) : null,
        ];
    }

    private static function nama(Collection $c, string $format): array
    {
        return $c->map(fn ($m) => strtr($format, ['{nim}' => $m->nim ?? '', '{nama}' => $m->nama_lengkap ?? $m->name ?? '', '{kode}' => $m->kode_mk ?? '', '{mk}' => $m->nama_mk ?? '']))->all();
    }

    // ------------------------------------------------------------------ pemeriksaan

    private function akademik(Collection $ta, ?TahunAkademik $aktif): void
    {
        if ($ta->count() === 0) {
            $this->tambah('ta_kosong', 'kritis', 'BAAK / Akademik', 'Belum ada Tahun Akademik yang aktif', 1,
                'KRS, jadwal, nilai, dan tagihan semester tidak dapat berjalan tanpa semester aktif.', ['rute' => 'admin.tahun-akademik.index']);
        } elseif ($ta->count() > 1) {
            $this->tambah('ta_ganda', 'kritis', 'BAAK / Akademik', 'Lebih dari satu Tahun Akademik ditandai aktif', $ta->count(),
                'Sistem hanya boleh memiliki satu semester aktif; data KRS/nilai bisa tercatat ke semester yang salah.', ['contoh' => $ta->pluck('nama_semester')->all(), 'rute' => 'admin.tahun-akademik.index']);
        }
        if ($aktif && (!$aktif->tanggal_mulai_krs || !$aktif->tanggal_selesai_krs) && !KalenderPengendali::acara('krs', $aktif->id)) {
            $this->tambah('krs_tanpa_tanggal', 'penting', 'BAAK / Akademik', 'Periode KRS semester aktif belum dijadwalkan', 1,
                'Tetapkan "Periode pengisian KRS" di Kalender Akademik agar KRS terbuka dan tertutup otomatis.', ['rute' => 'admin.kalender.create']);
        }

        $tanpaKurikulum = ProgramStudi::whereDoesntHave('kurikulums', fn ($q) => $q->where('is_active', true))->get();
        $this->tambah('prodi_tanpa_kurikulum', 'kritis', 'BAAK / Akademik', 'Program studi tanpa kurikulum aktif', $tanpaKurikulum->count(),
            'Mahasiswa prodi ini tidak mendapat penawaran mata kuliah saat mengisi KRS.', ['contoh' => $tanpaKurikulum->pluck('nama_prodi')->all(), 'rute' => 'admin.kurikulum.index']);

        $tanpaKaprodi = ProgramStudi::whereNull('kaprodi_dosen_id')->get();
        $this->tambah('prodi_tanpa_kaprodi', 'penting', 'Pimpinan / Admin', 'Program studi belum memiliki Kaprodi', $tanpaKaprodi->count(),
            'Validasi KRS oleh Kaprodi dan penetapan dosen wali tidak tersedia untuk prodi ini.', ['contoh' => $tanpaKaprodi->pluck('nama_prodi')->all(), 'rute' => 'admin.program-studi.index']);
    }

    private function perkuliahan(TahunAkademik $ta, ?string $tenggat): void
    {
        $semester = app(AcademicRuleService::class)->semesterDitawarkan($ta);
        $pivot = DB::table('dosen_mata_kuliah')->pluck('mata_kuliah_id')->unique();
        $ditawarkan = MataKuliah::with(['kurikulum', 'jadwals'])->whereIn('semester', $semester)
            ->whereHas('kurikulum', fn ($q) => $q->where('is_active', true))->get();

        foreach ($ditawarkan->groupBy(fn ($mk) => optional($mk->kurikulum)->program_studi_id) as $prodiId => $mks) {
            $prodi = $prodiId ? ProgramStudi::find($prodiId) : null;
            $bidangKaprodi = $prodi ? 'Kaprodi ' . $prodi->nama_prodi : 'BAAK / Akademik';
            $tujuan = $prodi ? 'kaprodi:' . $prodi->id : null;

            $tanpaDosen = $mks->filter(fn ($mk) => !$mk->dosen_id && !$pivot->contains($mk->id));
            $this->tambah('mk_tanpa_dosen_' . $prodiId, 'kritis', $bidangKaprodi, 'Mata kuliah ditawarkan ' . $ta->nama_semester . ' belum punya dosen pengampu', $tanpaDosen->count(),
                'Nilai tidak dapat diinput dan EDOM tidak dapat dilakukan untuk mata kuliah tanpa pengampu.',
                ['contoh' => self::nama($tanpaDosen, '{kode} {mk}'), 'tujuan' => $tujuan, 'tenggat' => $tenggat, 'rute' => 'admin.mata-kuliah.index']);

            $tanpaJadwal = $mks->filter(fn ($mk) => $mk->jadwals->isEmpty());
            $this->tambah('mk_tanpa_jadwal_' . $prodiId, 'kritis', 'BAAK / Akademik', 'Mata kuliah ditawarkan ' . ($prodi->nama_prodi ?? '') . ' belum punya jadwal/rombel', $tanpaJadwal->count(),
                'Mahasiswa tidak dapat memilih kelas saat KRS dan jadwal kuliah tidak muncul.',
                ['contoh' => self::nama($tanpaJadwal, '{kode} {mk}'), 'tenggat' => $tenggat, 'rute' => 'admin.mata-kuliah.index']);
        }

        $jadwalCacat = Jadwal::with('mataKuliah')->whereIn('mata_kuliah_id', $ditawarkan->pluck('id'))
            ->where(fn ($q) => $q->whereNull('ruang')->orWhere('ruang', '')->orWhereNull('kuota')->orWhere('kuota', '<=', 0))->get();
        $this->tambah('jadwal_tanpa_ruang', 'penting', 'BAAK / Akademik', 'Jadwal tanpa ruang atau kuota kursi', $jadwalCacat->count(),
            'Kuota kosong membuat kursi kelas paralel tidak terbatas; ruang kosong membingungkan mahasiswa.',
            ['contoh' => $jadwalCacat->map(fn ($j) => optional($j->mataKuliah)->kode_mk . ' kelas ' . $j->nama_kelas)->all(), 'tenggat' => $tenggat, 'rute' => 'admin.mata-kuliah.index']);
    }

    private function krsDanNilai(TahunAkademik $ta): void
    {
        $menunggu = KrsPersetujuan::with('mahasiswa')->where('tahun_akademik_id', $ta->id)->where('status', KrsPersetujuan::MENUNGGU)
            ->where('updated_at', '<', now()->subDays(3))->get()->filter(fn ($p) => $p->mahasiswa);
        foreach ($menunggu->groupBy(fn ($p) => $p->mahasiswa->program_studi_id) as $prodiId => $daftar) {
            $prodi = ProgramStudi::find($prodiId);
            $this->tambah('krs_menunggu_' . $prodiId, 'penting', 'Kaprodi ' . ($prodi->nama_prodi ?? '-') . ' & Dosen Wali', 'KRS menunggu persetujuan lebih dari 3 hari', $daftar->count(),
                'Mahasiswa belum bisa mengikuti kelas Verum dan jadwal kuliahnya belum tampil sebelum KRS disetujui.',
                ['contoh' => self::nama($daftar->pluck('mahasiswa'), '{nim} {nama}'), 'tujuan' => $prodi ? 'kaprodi:' . $prodi->id : null]);
        }

        $batasNilai = KalenderPengendali::acara('input_nilai', $ta->id);
        if ($batasNilai && KalenderPengendali::status($batasNilai) === 'selesai') {
            $kosong = DB::table('mahasiswa_mata_kuliah as mmk')
                ->join('mahasiswas as m', 'm.id', '=', 'mmk.mahasiswa_id')
                ->join('mata_kuliahs as mk', 'mk.id', '=', 'mmk.mata_kuliah_id')
                ->where('mmk.tahun_akademik_id', $ta->id)->whereNull('mmk.nilai')
                ->whereIn('mmk.mahasiswa_id', KrsPersetujuan::idsDisetujui($ta->id))
                ->select('m.program_studi_id', 'mk.kode_mk', 'mk.nama_mk')->get();
            foreach ($kosong->groupBy('program_studi_id') as $prodiId => $baris) {
                $prodi = ProgramStudi::find($prodiId);
                $this->tambah('nilai_kosong_' . $prodiId, 'penting', 'Kaprodi ' . ($prodi->nama_prodi ?? '-') . ' & Dosen Pengampu', 'Nilai belum diinput setelah periode input nilai berakhir', $baris->count(),
                    'KHS & IPS mahasiswa belum lengkap. Perpanjang "Periode input nilai" di Kalender bila dosen perlu waktu tambahan.',
                    ['contoh' => $baris->map(fn ($b) => $b->kode_mk . ' ' . $b->nama_mk)->unique()->values()->all(), 'tujuan' => $prodi ? 'kaprodi:' . $prodi->id : null]);
            }
        }

        if (!EvaluasiSesi::where('tahun_akademik_id', $ta->id)->exists()) {
            $this->tambah('edom_belum_ada', 'penting', 'Penjaminan Mutu', 'Sesi EDOM semester aktif belum dijadwalkan', 1,
                'Tetapkan "Periode evaluasi dosen (EDOM)" di Kalender agar evaluasi terbuka otomatis sebelum KHS/KRS semester berikutnya.',
                ['tujuan' => 'penjaminan_mutu', 'rute' => 'admin.kalender.create']);
        }
    }

    private function keuangan(TahunAkademik $ta, ?string $tenggat): void
    {
        $punya = Pembayaran::untukSemester($ta)->whereNotNull('mahasiswa_id')->pluck('mahasiswa_id');
        $tanpaTagihan = Mahasiswa::where('status_mahasiswa', 'Aktif')->whereNotIn('id', $punya)->orderBy('nim')->get(['id', 'nim', 'nama_lengkap']);
        $this->tambah('tanpa_tagihan', 'penting', 'Keuangan', 'Mahasiswa aktif belum memiliki tagihan ' . $ta->nama_semester, $tanpaTagihan->count(),
            'Tanpa tagihan semester, status keuangan mahasiswa tidak terpantau sebelum KRS.',
            ['contoh' => self::nama($tanpaTagihan, '{nim} {nama}'), 'tujuan' => 'keuangan', 'tenggat' => $tenggat]);

        $menunggu = Pembayaran::where('status', 'menunggu_konfirmasi')->where('updated_at', '<', now()->subDays(3))->count();
        $this->tambah('bukti_menunggu', 'penting', 'Keuangan', 'Bukti pembayaran menunggu verifikasi lebih dari 3 hari', $menunggu,
            'Mahasiswa tertahan di gerbang KRS sampai buktinya diverifikasi.', ['tujuan' => 'keuangan']);
    }

    private function mahasiswa(?string $tenggat): void
    {
        $aktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->with('programStudi')->orderBy('nim')->get();

        $nik = $aktif->filter(fn ($m) => !FeederExportController::nikValid($m->nik));
        $this->tambah('nik_tidak_valid', 'kritis', 'BAAK / Akademik', 'NIK mahasiswa kosong atau tidak valid (16 digit)', $nik->count(),
            'Data ditolak saat dilaporkan ke PDDIKTI (Neo Feeder).', ['contoh' => self::nama($nik, '{nim} {nama}'), 'rute' => 'admin.mahasiswa.index']);

        $wajib = $aktif->filter(fn ($m) => blank($m->nama_ibu_kandung) || $m->nama_ibu_kandung === 'Belum Diisi' || !$m->tanggal_lahir || blank($m->jenis_kelamin) || blank($m->tempat_lahir));
        $this->tambah('data_wajib_pddikti', 'kritis', 'BAAK / Akademik', 'Data wajib PDDIKTI belum lengkap (nama ibu, tempat/tanggal lahir, jenis kelamin)', $wajib->count(),
            'Kolom wajib Neo Feeder; mahasiswa tidak dapat dilaporkan.', ['contoh' => self::nama($wajib, '{nim} {nama}'), 'rute' => 'admin.mahasiswa.index']);

        foreach ($aktif->whereNull('dosen_wali_id')->groupBy('program_studi_id') as $prodiId => $daftar) {
            $prodi = $daftar->first()->programStudi;
            $this->tambah('tanpa_wali_' . $prodiId, 'penting', 'Kaprodi ' . ($prodi->nama_prodi ?? '-'), 'Mahasiswa aktif belum memiliki Dosen Wali', $daftar->count(),
                'KRS mahasiswa hanya dapat divalidasi Kaprodi; bimbingan akademik tidak berjalan.',
                ['contoh' => self::nama($daftar, '{nim} {nama}'), 'tujuan' => $prodi ? 'kaprodi:' . $prodi->id : null, 'tenggat' => $tenggat]);
        }

        $tanpaAkun = $aktif->whereNull('user_id');
        $this->tambah('mhs_tanpa_akun', 'penting', 'Admin Sistem', 'Mahasiswa aktif tanpa akun login', $tanpaAkun->count(),
            'Mahasiswa tidak dapat mengisi KRS, melihat KHS, atau memakai aplikasi.', ['contoh' => self::nama($tanpaAkun, '{nim} {nama}'), 'rute' => 'admin.mahasiswa.index']);

        $lulus = Mahasiswa::where('status_mahasiswa', 'Lulus')->whereNull('tanggal_lulus')->get(['nim', 'nama_lengkap']);
        $this->tambah('lulus_tanpa_tanggal', 'penting', 'BAAK / Akademik', 'Alumni tanpa tanggal lulus', $lulus->count(),
            'Masa studi, tepat waktu, dan tracer study untuk akreditasi tidak dapat dihitung.', ['contoh' => self::nama($lulus, '{nim} {nama}'), 'rute' => 'admin.mahasiswa.index']);

        $foto = $aktif->filter(fn ($m) => blank($m->foto_profil));
        $this->tambah('mhs_tanpa_foto', 'saran', 'BAAK / Kemahasiswaan', 'Mahasiswa aktif belum memiliki pas foto', $foto->count(),
            'E-KTM menampilkan inisial nama, bukan foto.', ['contoh' => self::nama($foto, '{nim} {nama}')]);

        $hp = $aktif->filter(fn ($m) => blank($m->nomor_telepon));
        $this->tambah('mhs_tanpa_hp', 'saran', 'BAAK / Kemahasiswaan', 'Mahasiswa aktif tanpa nomor telepon', $hp->count(),
            'Menyulitkan pemberitahuan mendesak (KRS ditolak, tagihan).', ['contoh' => self::nama($hp, '{nim} {nama}')]);
    }

    private function dosen(): void
    {
        $dosen = Dosen::orderBy('nama_lengkap')->get();
        $tanpaId = $dosen->filter(fn ($d) => blank($d->nidn) && blank($d->nuptk));
        $this->tambah('dosen_tanpa_nidn', 'penting', 'BAAK / Kepegawaian', 'Dosen tanpa NIDN maupun NUPTK', $tanpaId->count(),
            'Dosen tidak dapat dilaporkan ke PDDIKTI dan tidak terhitung untuk akreditasi.', ['contoh' => self::nama($tanpaId, '{nama}'), 'rute' => 'admin.dosen.index']);

        $tanpaStatus = $dosen->filter(fn ($d) => blank($d->status_kepegawaian));
        $this->tambah('dosen_tanpa_status', 'penting', 'BAAK / Kepegawaian', 'Dosen tanpa status kepegawaian (tetap / tidak tetap)', $tanpaStatus->count(),
            'Rasio dosen tetap : mahasiswa untuk akreditasi bisa salah hitung.', ['contoh' => self::nama($tanpaStatus, '{nama}'), 'tujuan' => 'penjaminan_mutu', 'rute' => 'admin.dosen.index']);

        $tanpaHomebase = $dosen->whereNull('program_studi_id');
        $this->tambah('dosen_tanpa_homebase', 'penting', 'BAAK / Kepegawaian', 'Dosen tanpa homebase program studi', $tanpaHomebase->count(),
            'Dosen tidak terhitung sebagai DTPS prodi mana pun pada indikator akreditasi.', ['contoh' => self::nama($tanpaHomebase, '{nama}'), 'rute' => 'admin.dosen.index']);
    }

    private function sistem(): void
    {
        if (Schema::hasTable('lokasi_kerja') && DB::table('lokasi_kerja')->count() === 0) {
            $this->tambah('lokasi_kerja', 'penting', 'Admin Sistem', 'Lokasi kerja absensi belum diatur', 1,
                'Absensi GPS pegawai & dosen tidak dapat memvalidasi radius kampus.', ['rute' => 'admin.absensi.lokasi.index']);
        }
        $detak = Cache::get('siakad-penjadwal-terakhir');
        if (!$detak || now()->timestamp - $detak > 2 * 86400) {
            $this->tambah('penjadwal_mati', 'penting', 'Admin Sistem', 'Penjadwal otomatis server tidak berjalan', 1,
                'Pengingat kalender H-3/H-1, notifikasi mingguan, dan pengingat kuliah harian tidak terkirim. Pasang cron: * * * * * php artisan schedule:run', []);
        }

        $tanpaPeran = User::doesntHave('roles')->orderBy('name')->get(['name', 'email']);
        $this->tambah('akun_tanpa_peran', 'saran', 'Admin Sistem', 'Akun tanpa peran', $tanpaPeran->count(),
            'Akun ini tidak dapat membuka modul apa pun setelah login.', ['contoh' => $tanpaPeran->map(fn ($u) => $u->name . ' (' . $u->email . ')')->all(), 'rute' => 'admin.user.index']);
    }
}
