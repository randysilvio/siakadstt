<?php

namespace App\Support;

use App\Models\Pengaturan;

/**
 * Satu sumber penjelasan aturan & rumus yang dipakai SIAKAD. Teks ini tampil sebagai ikon "?" kecil di setiap
 * halaman yang menghitung angka atau menerapkan aturan, dan dirangkum di halaman Admin › Aturan & Perhitungan.
 *
 * Jenis dasar:
 *  - peraturan  : mengikuti peraturan perundang-undangan / standar nasional
 *  - kebijakan  : kebijakan internal kampus yang sudah ditetapkan di sistem
 *  - konfirmasi : angka/rumus bawaan sistem yang BELUM dikonfirmasi pimpinan kampus
 */
class InfoAturan
{
    public const JENIS = [
        'peraturan'  => ['label' => 'Peraturan', 'warna' => 'success'],
        'kebijakan'  => ['label' => 'Kebijakan Kampus', 'warna' => 'primary'],
        'konfirmasi' => ['label' => 'Perlu Konfirmasi', 'warna' => 'warning'],
    ];

    /** @return array<string, array{modul:string, judul:string, isi:array<int,string>, dasar:string, jenis:string, halaman:string}> */
    public static function semua(): array
    {
        $b = app(\App\Services\AcademicRuleService::class)->bobotPenilaian();
        $pct = fn ($k) => rtrim(rtrim(number_format($b[$k] * 100, 2, ',', ''), '0'), ',');
        $istilahWali = Pengaturan::get('istilah_pembimbing', 'Dosen Wali');

        return [
            // ----------------------------------------------------------------- AKADEMIK
            'krs_batas_sks' => [
                'modul' => 'KRS', 'halaman' => 'Pengisian KRS', 'jenis' => 'konfirmasi',
                'judul' => 'Batas SKS per semester',
                'isi' => [
                    'Batas dihitung dari IPK kumulatif: IPK ≥ 3,00 → 24 SKS; 2,50–2,99 → 21 SKS; 2,00–2,49 → 18 SKS; < 2,00 → 15 SKS.',
                    'Mahasiswa yang belum punya nilai (semester 1) diberi 24 SKS.',
                ],
                'dasar' => 'SN Dikti (Permendikbudristek 53/2023 Pasal 18) membatasi semester 1–2 paling banyak 20 SKS dan semester 3 dst. paling banyak 24 SKS. Tingkatan berdasarkan IPK adalah kebijakan kampus.',
            ],
            'krs_aturan' => [
                'modul' => 'KRS', 'halaman' => 'Pengisian KRS', 'jenis' => 'kebijakan',
                'judul' => 'Syarat mata kuliah dapat diambil',
                'isi' => [
                    'Hanya mata kuliah kurikulum AKTIF prodi sendiri pada semester ganjil/genap yang sedang berjalan.',
                    'Prasyarat dianggap lulus bila nilainya A, B, C, atau D.',
                    'Kelas paralel (rombel) dipilih selama kuota kursi masih ada dan jadwalnya tidak bentrok.',
                ],
                'dasar' => 'Pedoman akademik kampus.',
            ],
            'krs_gerbang' => [
                'modul' => 'KRS', 'halaman' => 'KRS & Riwayat Pembayaran', 'jenis' => 'kebijakan',
                'judul' => 'Syarat membuka KRS',
                'isi' => [
                    'Seluruh tagihan berstatus LUNAS atau DISPENSASI. Bukti transfer yang belum diverifikasi Keuangan belum membuka KRS.',
                    'Status mahasiswa Aktif, berada dalam rentang tanggal KRS, dan saklar KRS tidak ditutup Admin.',
                    'Evaluasi Dosen (EDOM) sesi yang sedang dibuka sudah diisi.',
                    'Mahasiswa yang belum memiliki tagihan sama sekali tidak tertahan oleh syarat keuangan.',
                ],
                'dasar' => 'Kebijakan kampus.',
            ],
            'krs_persetujuan' => [
                'modul' => 'KRS', 'halaman' => 'Perwalian & Validasi KRS Kaprodi', 'jenis' => 'kebijakan',
                'judul' => 'Persetujuan KRS',
                'isi' => [
                    "Cukup disetujui salah satu: {$istilahWali} atau Kaprodi prodi mahasiswa.",
                    'KRS yang sudah disetujui terkunci; hanya validator yang menyetujui atau Admin yang dapat membukanya kembali.',
                ],
                'dasar' => 'Kebijakan kampus.',
            ],
            'nilai_akhir' => [
                'modul' => 'Nilai', 'halaman' => 'Buku Nilai & Pengaturan', 'jenis' => 'konfirmasi',
                'judul' => 'Nilai akhir & huruf mutu',
                'isi' => [
                    "Nilai akhir = Kehadiran {$pct('hadir')}% + Tugas {$pct('tugas')}% + UTS {$pct('uts')}% + UAS {$pct('uas')}% (bobot diatur di menu Pengaturan; bila totalnya bukan 100% dipakai 10/20/30/40).",
                    'Huruf mutu: A ≥ 80; B 70–79,99; C 60–69,99; D 50–59,99; E < 50. Huruf baru muncul setelah UTS atau UAS diisi.',
                    'Dosen dapat menetapkan huruf secara manual; huruf manual tidak ditimpa perhitungan otomatis.',
                ],
                'dasar' => 'SN Dikti (Permendikbudristek 53/2023 Pasal 28) menetapkan bobot A=4, B=3, C=2, D=1, E=0 dan membolehkan huruf antara (AB, BC, dst.). Batas angka ke huruf dan bobot komponen adalah kebijakan kampus.',
            ],
            'ips' => [
                'modul' => 'Nilai', 'halaman' => 'KHS', 'jenis' => 'peraturan',
                'judul' => 'Indeks Prestasi Semester (IPS)',
                'isi' => [
                    'IPS = Σ(SKS × bobot huruf) ÷ Σ SKS mata kuliah yang sudah bernilai pada semester tersebut.',
                    'Bobot: A=4, B=3, C=2, D=1, E=0. Mata kuliah yang belum dinilai tidak dihitung.',
                ],
                'dasar' => 'SN Dikti, Permendikbudristek 53/2023 Pasal 28.',
            ],
            'ipk' => [
                'modul' => 'Nilai', 'halaman' => 'KHS, Transkrip, Dashboard, Perwalian, Kaprodi', 'jenis' => 'konfirmasi',
                'judul' => 'Indeks Prestasi Kumulatif (IPK) & SKS lulus',
                'isi' => [
                    'IPK = Σ(SKS × bobot) ÷ Σ SKS seluruh mata kuliah bernilai, termasuk nilai E.',
                    'Mata kuliah yang diulang dihitung SATU kali memakai nilai terbaik.',
                    'SKS lulus = SKS mata kuliah bernilai A–D (nilai E tidak dihitung lulus).',
                ],
                'dasar' => 'Rumus IPK mengikuti SN Dikti (Pasal 28). Aturan nilai terbaik untuk mata kuliah ulang dan nilai D dihitung lulus adalah kebijakan kampus. Syarat lulus program sarjana IPK ≥ 2,00 (Pasal 30).',
            ],
            'ews' => [
                'modul' => 'Nilai', 'halaman' => 'Dashboard Kaprodi & Perwalian', 'jenis' => 'konfirmasi',
                'judul' => 'Peringatan dini akademik',
                'isi' => [
                    'Mahasiswa aktif yang sudah memiliki nilai dan IPK < 2,00 ditandai merah sebagai rawan.',
                ],
                'dasar' => 'Ambang 2,00 mengikuti syarat minimal IPK kelulusan sarjana (SN Dikti Pasal 30). Penetapan "rawan DO" dan tindak lanjutnya mengikuti peraturan akademik kampus.',
            ],
            'edom' => [
                'modul' => 'EDOM', 'halaman' => 'Evaluasi Dosen & Hasil EDOM', 'jenis' => 'kebijakan',
                'judul' => 'Evaluasi Dosen oleh Mahasiswa (EDOM)',
                'isi' => [
                    'Skala jawaban 1–4 (1 = kurang, 4 = sangat baik). Skor dosen = rata-rata seluruh jawaban skala.',
                    'Wajib diisi untuk setiap mata kuliah KRS yang disetujui pada semester sesi evaluasi dan punya dosen pengampu.',
                    'Selama sesi EDOM dibuka dan belum diisi, KRS, KHS, dan transkrip ditahan.',
                ],
                'dasar' => 'Sistem penjaminan mutu internal kampus.',
            ],

            // ----------------------------------------------------------------- DOSEN / BKD
            'bkd_total' => [
                'modul' => 'BKD', 'halaman' => 'Dashboard Dosen, Tridharma & BKD, Laporan Mutu', 'jenis' => 'peraturan',
                'judul' => 'Total Beban Kerja Dosen',
                'isi' => [
                    'Dihitung per semester aktif: Pendidikan + Penelitian + Pengabdian + Penunjang.',
                    '< 12 SKS = kurang; 12–16 SKS = memenuhi; > 16 SKS = beban lebih.',
                    'Komposisi minimal per unsur belum diperiksa sistem.',
                ],
                'dasar' => 'UU 14/2005 Pasal 72, PP 37/2009, PO BKD (Kepdirjen Dikti 12/E/KPT/2021), dan Permendiktisaintek 52/2025: paling sedikit setara 12 SKS, paling banyak 16 SKS. Bagi dosen tetap yayasan/gereja, rinciannya mengikuti rubrik BKD yang dipakai asesor kampus.',
            ],
            'bkd_pendidikan' => [
                'modul' => 'BKD', 'halaman' => 'Tridharma & BKD', 'jenis' => 'konfirmasi',
                'judul' => 'Unsur Pendidikan',
                'isi' => [
                    'Pengajaran: SKS penuh setiap mata kuliah semester aktif yang diampu (dosen utama maupun anggota team teaching). Kelas paralel dihitung satu kali.',
                    'Perwalian: 0,1 SKS per mahasiswa wali aktif, paling banyak 2 SKS.',
                ],
                'dasar' => 'Angka perwalian dan cara menghitung team teaching/kelas paralel adalah bawaan sistem. Contoh rubrik PO BKD di PT lain: pembimbing akademik 2 SKS per semester tanpa melihat jumlah mahasiswa, dan pengajaran dihitung per kelas.',
            ],
            'bkd_tridharma' => [
                'modul' => 'BKD', 'halaman' => 'Tridharma & BKD', 'jenis' => 'konfirmasi',
                'judul' => 'Penelitian, Pengabdian & Penunjang',
                'isi' => [
                    'SKS diisi sendiri oleh dosen per kegiatan (0–12 SKS) dan dihitung bila tahun & semesternya sama dengan semester aktif.',
                    'SK kepanitiaan berstatus Selesai otomatis menambah penunjang 0,5 SKS per SK, paling banyak 3 SKS (saat ini dihitung dari seluruh SK, belum per semester, dan belum membedakan ketua/anggota).',
                ],
                'dasar' => 'Nilai SKS per kegiatan seharusnya mengikuti rubrik BKD (contoh rubrik PT lain: kepanitiaan 0,25–0,75 SKS sesuai tingkat & jabatan). Belum ada verifikasi asesor di sistem.',
            ],
            'bkd_kum' => [
                'modul' => 'BKD', 'halaman' => 'Tridharma & BKD', 'jenis' => 'konfirmasi',
                'judul' => 'Estimasi angka kredit (KUM)',
                'isi' => [
                    'KUM = angka kredit yang diisi dosen + (SKS pendidikan × 1,5) + (jumlah SK × 1).',
                    'Ini hanya ESTIMASI internal, bukan angka kredit resmi jabatan fungsional.',
                ],
                'dasar' => 'Rumus bawaan sistem tanpa dasar peraturan. Angka kredit resmi ditetapkan melalui Penilaian Angka Kredit (PAK) oleh tim penilai.',
            ],

            // ----------------------------------------------------------------- KEUANGAN, PMB, LAINNYA
            'keu_piutang' => [
                'modul' => 'Keuangan', 'halaman' => 'Dashboard Rektorat', 'jenis' => 'kebijakan',
                'judul' => 'Tagihan, pendapatan & piutang semester',
                'isi' => [
                    'Total tagihan = seluruh tagihan berperiode semester aktif (label baku maupun label lama).',
                    'Pendapatan = tagihan LUNAS. Piutang = tagihan yang belum lunas, termasuk dispensasi/cicilan dan bukti yang belum diverifikasi.',
                    'Tren pendapatan dikelompokkan per tahun tanggal bayar.',
                ],
                'dasar' => 'Kebijakan pelaporan keuangan kampus.',
            ],
            'pmb_nim' => [
                'modul' => 'PMB', 'halaman' => 'Detail Pendaftar PMB', 'jenis' => 'kebijakan',
                'judul' => 'Format NIM mahasiswa baru',
                'isi' => [
                    'NIM = tahun PMB (angkatan) + kode prodi + nomor urut 3 digit, contoh 2027TH001.',
                    'Angkatan (tahun masuk) mengikuti Tahun PMB gelombang, bukan tanggal saat diluluskan.',
                ],
                'dasar' => 'Kebijakan kampus.',
            ],
            'mutu_rasio' => [
                'modul' => 'Mutu', 'halaman' => 'Dashboard Penjaminan Mutu', 'jenis' => 'konfirmasi',
                'judul' => 'Rasio dosen & rata-rata IPK',
                'isi' => [
                    'Rasio = jumlah mahasiswa aktif ÷ jumlah seluruh data dosen (termasuk dosen tidak tetap).',
                    'Rata-rata IPK dihitung dari mahasiswa aktif yang IPK-nya di atas 0.',
                ],
                'dasar' => 'Untuk akreditasi, rasio biasanya dihitung terhadap dosen tetap sesuai instrumen BAN-PT/LAM. Konfirmasi definisi yang dipakai kampus.',
            ],
            'tracer' => [
                'modul' => 'Tracer Study', 'halaman' => 'Rekap Tracer Study', 'jenis' => 'kebijakan',
                'judul' => 'Indikator tracer study',
                'isi' => [
                    'Waktu tunggu = rata-rata bulan sampai pekerjaan/pelayanan pertama.',
                    'Kesesuaian bidang = persentase jawaban "Sangat Sesuai" dan "Sesuai". Persentase bekerja = selain "Belum Bekerja".',
                ],
                'dasar' => 'Indikator umum tracer study untuk akreditasi.',
            ],
            'absensi' => [
                'modul' => 'Absensi', 'halaman' => 'Pengaturan & Lokasi Absensi', 'jenis' => 'kebijakan',
                'judul' => 'Keterlambatan & radius absensi',
                'isi' => [
                    'Check-in setelah (jam masuk + toleransi) tetap tercatat Hadir dengan keterangan Terlambat.',
                    'Check-in/out hanya diterima bila posisi GPS berada di dalam radius salah satu lokasi kerja.',
                ],
                'dasar' => 'Peraturan kepegawaian yayasan/gereja.',
            ],
        ];
    }

    public static function ambil(string $kunci): ?array
    {
        return self::semua()[$kunci] ?? null;
    }
}
