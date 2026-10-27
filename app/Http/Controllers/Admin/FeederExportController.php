<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Pengaturan;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeederExportController extends Controller
{
    /**
     * Menampilkan halaman Pusat Sinkronisasi Neo Feeder PDDIKTI & Backup Database.
     */
    public function index(Request $request)
    {
        $tahunAkademiks = TahunAkademik::orderBy('tahun', 'desc')->orderBy('id', 'desc')->get();
        $periodeAktif = TahunAkademik::where('is_active', true)->first() ?? $tahunAkademiks->first();
        $selectedTaId = $request->input('tahun_akademik_id', optional($periodeAktif)->id);
        $selectedTa = TahunAkademik::find($selectedTaId) ?? $periodeAktif;

        $programStudis = ProgramStudi::orderBy('nama_prodi')->get();

        // Audit kesiapan data standar PDDIKTI
        $totalMhsAktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        // NIK valid: 16 digit angka dengan kode provinsi Dukcapil (menangkap juga NIK buatan dari PMB lama)
        $mhsTanpaNik = Mahasiswa::where('status_mahasiswa', 'Aktif')->pluck('nik')
            ->filter(fn ($nik) => !self::nikValid($nik))
            ->count();

        $mhsTanpaIbu = Mahasiswa::where('status_mahasiswa', 'Aktif')
            ->where(function ($q) {
                $q->whereNull('nama_ibu_kandung')->orWhere('nama_ibu_kandung', '')->orWhere('nama_ibu_kandung', 'Belum Diisi');
            })->count();

        $totalDosen = Dosen::count();
        $dosenTanpaNidn = Dosen::whereNull('nidn')->orWhere('nidn', '')->count();

        $kodePt = Pengaturan::get('kode_pt_pddikti', '143045');

        return view('admin.feeder.index', compact(
            'tahunAkademiks',
            'selectedTa',
            'programStudis',
            'totalMhsAktif',
            'mhsTanpaNik',
            'mhsTanpaIbu',
            'totalDosen',
            'dosenTanpaNidn',
            'kodePt'
        ));
    }

    /**
     * Mengonversi TahunAkademik ke format 5 digit kode semester PDDIKTI (Contoh: 2025/2026 Ganjil -> 20251).
     */
    private function formatKodeSemester(?TahunAkademik $ta): string
    {
        if (!$ta) {
            return date('Y') . '1';
        }
        $tahunAwal = substr(preg_replace('/[^0-9]/', '', $ta->tahun), 0, 4) ?: date('Y');
        $smtDigit = in_array(strtolower($ta->semester), ['ganjil', 'gasal', '1']) ? '1' : '2';
        return $tahunAwal . $smtDigit;
    }

    /**
     * Mengekspor CSV Standar Neo Feeder PDDIKTI berdasarkan kategori yang dipilih.
     */
    public function exportCsv(Request $request, string $kategori): StreamedResponse
    {
        $ta = TahunAkademik::find($request->input('tahun_akademik_id'))
            ?? TahunAkademik::where('is_active', true)->first();
        $prodiId = $request->input('program_studi_id');
        $kodeSemester = $this->formatKodeSemester($ta);

        $filename = "feeder_{$kategori}_{$kodeSemester}_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($kategori, $ta, $prodiId, $kodeSemester) {
            $out = fopen('php://output', 'w');
            // Tambahkan UTF-8 BOM agar langsung rapi saat dibuka di Microsoft Excel maupun Neo Feeder Importer
            fwrite($out, "\xEF\xBB\xBF");

            if ($kategori === 'mahasiswa') {
                fputcsv($out, self::amankanCsv([
                    'NIM', 'Nama Mahasiswa', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin',
                    'NIK', 'NISN', 'Agama', 'Nama Ibu Kandung', 'Alamat', 'RT', 'RW',
                    'Dusun', 'Kelurahan', 'Kecamatan', 'Kode Pos', 'Jenis Tinggal',
                    'Alat Transportasi', 'No HP', 'Email', 'Kode Prodi', 'Nama Prodi',
                    'Tahun Masuk', 'Semester Mulai', 'Status Mahasiswa'
                ]), ';');

                $query = Mahasiswa::with(['programStudi', 'user'])->orderBy('nim');
                if ($prodiId) {
                    $query->where('program_studi_id', $prodiId);
                }

                foreach ($query->cursor() as $mhs) {
                    fputcsv($out, self::amankanCsv([
                        $mhs->nim,
                        $mhs->nama_lengkap,
                        $mhs->tempat_lahir ?? '',
                        $mhs->tanggal_lahir ? $mhs->tanggal_lahir->format('Y-m-d') : '',
                        $mhs->jenis_kelamin ?? 'L',
                        $mhs->nik ?? '',
                        $mhs->nisn ?? '',
                        $mhs->agama ?? 'Kristen Protestan',
                        $mhs->nama_ibu_kandung ?? '',
                        $mhs->alamat ?? '',
                        $mhs->rt ?? '00',
                        $mhs->rw ?? '00',
                        $mhs->dusun ?? '',
                        $mhs->kelurahan ?? '',
                        $mhs->kecamatan ?? '',
                        $mhs->kode_pos ?? '',
                        $mhs->jenis_tinggal ?? '',
                        $mhs->alat_transportasi ?? '',
                        $mhs->nomor_telepon ?? '',
                        optional($mhs->user)->email ?? '',
                        optional($mhs->programStudi)->kode_prodi ?? '',
                        optional($mhs->programStudi)->nama_prodi ?? '',
                        $mhs->tahun_masuk,
                        ($mhs->tahun_masuk ?: date('Y')) . '1',
                        $mhs->status_mahasiswa,
                    ]), ';');
                }
            } elseif ($kategori === 'kelas_kuliah') {
                fputcsv($out, self::amankanCsv([
                    'Semester', 'Kode Mata Kuliah', 'Nama Mata Kuliah', 'Nama Kelas',
                    'Bobot SKS', 'Kapasitas Kuota', 'Ruang Kuliah', 'Dosen Pengampu Utama'
                ]), ';');

                $matkuls = MataKuliah::with(['jadwals', 'dosen', 'kurikulum.programStudi'])
                    ->orderBy('semester')
                    ->orderBy('kode_mk')
                    ->get();

                foreach ($matkuls as $mk) {
                    if ($mk->jadwals->isNotEmpty()) {
                        foreach ($mk->jadwals as $j) {
                            fputcsv($out, self::amankanCsv([
                                $kodeSemester,
                                $mk->kode_mk,
                                $mk->nama_mk,
                                $j->nama_kelas ?? 'A',
                                $mk->sks,
                                $j->kuota ?? 40,
                                $j->ruang ?? '-',
                                optional($mk->dosen)->nama_lengkap ?? '-',
                            ]), ';');
                        }
                    } else {
                        fputcsv($out, self::amankanCsv([
                            $kodeSemester,
                            $mk->kode_mk,
                            $mk->nama_mk,
                            'A',
                            $mk->sks,
                            40,
                            '-',
                            optional($mk->dosen)->nama_lengkap ?? '-',
                        ]), ';');
                    }
                }
            } elseif ($kategori === 'krs_nilai') {
                fputcsv($out, self::amankanCsv([
                    'NIM', 'Nama Mahasiswa', 'Semester', 'Kode Mata Kuliah', 'Nama Mata Kuliah',
                    'Nama Kelas', 'Bobot SKS', 'Nilai Angka', 'Nilai Huruf', 'Nilai Indeks', 'Kode Prodi'
                ]), ';');

                $bobotMap = ['A' => 4.00, 'B' => 3.00, 'C' => 2.00, 'D' => 1.00, 'E' => 0.00];

                $rows = DB::table('mahasiswa_mata_kuliah as mmk')
                    ->join('mahasiswas as m', 'm.id', '=', 'mmk.mahasiswa_id')
                    ->join('mata_kuliahs as mk', 'mk.id', '=', 'mmk.mata_kuliah_id')
                    // Hanya KRS yang disetujui pada semester tersebut yang dilaporkan ke PDDIKTI
                    ->join('krs_persetujuans as kp', function ($j) {
                        $j->on('kp.mahasiswa_id', '=', 'mmk.mahasiswa_id')
                          ->on('kp.tahun_akademik_id', '=', 'mmk.tahun_akademik_id')
                          ->where('kp.status', '=', 'Disetujui');
                    })
                    ->leftJoin('program_studis as ps', 'ps.id', '=', 'm.program_studi_id')
                    ->when($ta, fn($q) => $q->where('mmk.tahun_akademik_id', $ta->id))
                    ->when($prodiId, fn($q) => $q->where('m.program_studi_id', $prodiId))
                    ->select(
                        'm.nim',
                        'm.nama_lengkap',
                        'mk.kode_mk',
                        'mk.nama_mk',
                        'mk.sks',
                        'mmk.nama_kelas',
                        'mmk.nilai_akhir_angka',
                        'mmk.nilai',
                        'ps.kode_prodi'
                    )
                    ->orderBy('m.nim')
                    ->orderBy('mk.kode_mk')
                    ->get();

                foreach ($rows as $r) {
                    $huruf = strtoupper(trim((string) $r->nilai));
                    $indeks = $bobotMap[$huruf] ?? '';
                    fputcsv($out, self::amankanCsv([
                        $r->nim,
                        $r->nama_lengkap,
                        $kodeSemester,
                        $r->kode_mk,
                        $r->nama_mk,
                        $r->nama_kelas ?? 'A',
                        $r->sks,
                        $r->nilai_akhir_angka !== null ? number_format((float) $r->nilai_akhir_angka, 2, '.', '') : '',
                        $huruf,
                        $indeks !== '' ? number_format((float) $indeks, 2, '.', '') : '',
                        $r->kode_prodi ?? '',
                    ]), ';');
                }
            } elseif ($kategori === 'akm') {
                fputcsv($out, self::amankanCsv([
                    'NIM', 'Nama Mahasiswa', 'Semester', 'Status Mahasiswa (A/C/L/N)',
                    'IPS', 'IPK', 'SKS Semester', 'Total SKS Kumulatif', 'Kode Prodi'
                ]), ';');

                $query = Mahasiswa::with(['programStudi'])->orderBy('nim');
                if ($prodiId) {
                    $query->where('program_studi_id', $prodiId);
                }

                $statusKodeMap = [
                    'Aktif'     => 'A',
                    'Cuti'      => 'C',
                    'Lulus'     => 'L',
                    'Non-Aktif' => 'N',
                    'Drop Out'  => 'K',
                ];

                foreach (Mahasiswa::isiRekapNilai($query->get()) as $mhs) {
                    $ipsData = $ta ? $mhs->hitungIps($ta->id) : ['ips' => 0.00, 'total_sks' => 0];
                    $ipk = $mhs->hitungIpk();
                    $totalSksKumulatif = $mhs->totalSksLulus();
                    $kodeSt = $statusKodeMap[$mhs->status_mahasiswa] ?? 'A';

                    fputcsv($out, self::amankanCsv([
                        $mhs->nim,
                        $mhs->nama_lengkap,
                        $kodeSemester,
                        $kodeSt,
                        number_format((float) $ipsData['ips'], 2, '.', ''),
                        number_format((float) $ipk, 2, '.', ''),
                        $ipsData['total_sks'],
                        $totalSksKumulatif,
                        optional($mhs->programStudi)->kode_prodi ?? '',
                    ]), ';');
                }
            } elseif ($kategori === 'ajar_dosen') {
                fputcsv($out, self::amankanCsv([
                    'NIDN / NUPTK', 'Nama Dosen', 'Semester', 'Kode Mata Kuliah',
                    'Nama Mata Kuliah', 'Nama Kelas', 'Beban SKS', 'Rencana Tatap Muka', 'Realisasi Tatap Muka'
                ]), ';');

                $matkuls = MataKuliah::with(['dosen', 'teamDosens', 'jadwals'])
                    ->orderBy('kode_mk')
                    ->get();

                foreach ($matkuls as $mk) {
                    $kelasList = $mk->jadwals->pluck('nama_kelas')->filter()->unique()->values();
                    if ($kelasList->isEmpty()) {
                        $kelasList = collect(['A']);
                    }

                    $pengampus = $mk->teamDosens->isNotEmpty() ? $mk->teamDosens : collect([$mk->dosen])->filter();
                    $jmlPengampu = max(1, $pengampus->count());
                    $sksPerDosen = round($mk->sks / $jmlPengampu, 2);

                    foreach ($kelasList as $namaKls) {
                        foreach ($pengampus as $dsn) {
                            fputcsv($out, self::amankanCsv([
                                $dsn->nidn ?? '',
                                $dsn->nama_lengkap ?? '',
                                $kodeSemester,
                                $mk->kode_mk,
                                $mk->nama_mk,
                                $namaKls,
                                number_format($sksPerDosen, 2, '.', ''),
                                16,
                                16,
                            ]), ';');
                        }
                    }
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * 1-Click Full Database SQL Backup (.sql) Murni PHP/PDO (Jalan di Semua Hosting/Windows/Linux).
     */
    public function backupDatabaseSql(): StreamedResponse
    {
        $dbName = config('database.connections.mysql.database', 'siakad');
        $filename = "backup_db_{$dbName}_" . date('Y-m-d_His') . ".sql";

        return response()->streamDownload(function () use ($dbName) {
            $out = fopen('php://output', 'w');

            fwrite($out, "-- =========================================================\n");
            fwrite($out, "-- SIAKAD ENTERPRISE FULL DATABASE BACKUP\n");
            fwrite($out, "-- Database : {$dbName}\n");
            fwrite($out, "-- Tanggal  : " . now()->format('Y-m-d H:i:s') . " WIT\n");
            fwrite($out, "-- =========================================================\n\n");
            fwrite($out, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($out, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n");

            $tables = DB::select('SHOW TABLES');
            $pdo = DB::connection()->getPdo();

            foreach ($tables as $tableObj) {
                $tableArray = (array) $tableObj;
                $tableName = reset($tableArray);

                fwrite($out, "-- ---------------------------------------------------------\n");
                fwrite($out, "-- Struktur & Data untuk tabel `{$tableName}`\n");
                fwrite($out, "-- ---------------------------------------------------------\n");
                fwrite($out, "DROP TABLE IF EXISTS `{$tableName}`;\n");

                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createTable)) {
                    $createArr = (array) $createTable[0];
                    $createSql = end($createArr);
                    fwrite($out, $createSql . ";\n\n");
                }

                $rows = DB::table($tableName)->cursor();
                foreach ($rows as $row) {
                    $rowArr = (array) $row;
                    $cols = array_map(fn($c) => "`" . str_replace("`", "``", $c) . "`", array_keys($rowArr));
                    $vals = array_map(function ($v) use ($pdo) {
                        if (is_null($v)) {
                            return 'NULL';
                        }
                        return $pdo->quote((string) $v);
                    }, array_values($rowArr));

                    fwrite($out, "INSERT INTO `{$tableName}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n");
                }
                fwrite($out, "\n");
            }

            fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($out);
        }, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Mencegah CSV/Formula injection saat file dibuka di Excel: sel teks yang diawali = + - @ diberi apostrof.
     */
    private static function amankanCsv(array $baris): array
    {
        return array_map(function ($nilai) {
            if (is_string($nilai) && $nilai !== '' && in_array($nilai[0], ['=', '+', '-', '@', "\t", "\r"], true) && !is_numeric($nilai)) {
                return "'" . $nilai;
            }
            return $nilai;
        }, $baris);
    }

    public static function nikValid($nik): bool
    {
        $nik = trim((string) $nik);
        if (!preg_match('/^\d{16}$/', $nik)) {
            return false;
        }
        // Kode provinsi Dukcapil yang berlaku (termasuk provinsi baru di Tanah Papua 93–96)
        $kodeProvinsi = array_merge(range(11, 19), [21], range(31, 36), range(51, 53), range(61, 65), range(71, 76), [81, 82], range(91, 96));
        return in_array((int) substr($nik, 0, 2), $kodeProvinsi, true);
    }
}
