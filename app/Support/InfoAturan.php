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
                'modul' => 'KRS', 'halaman' => 'Pengisian KRS', 'jenis' => 'kebijakan',
                'judul' => 'Batas SKS per semester',
                'isi' => [
                    'Semester ke-1: paling banyak 20 SKS.',
                    'Semester ke-2 dst. menurut IPK kumulatif: IPK ≥ 3,00 → 24 SKS; 2,50–2,99 → 21 SKS; 2,00–2,49 → 18 SKS; < 2,00 → 15 SKS.',
                    'Semester ke-n = jumlah semester sebelumnya yang benar-benar ditempuh (KRS disetujui atau sudah bernilai) + 1; semester cuti tidak dihitung. Bila nilai semester sebelumnya belum keluar, batasnya 20 SKS.',
                ],
                'dasar' => 'Kebijakan kampus mengacu SN Dikti (Permendikbudristek 53/2023 Pasal 18: paling banyak 24 SKS per semester). Catatan: SN Dikti membatasi semester 1 dan 2 paling banyak 20 SKS; kampus menetapkan semester 2 mengikuti IPK hingga 24 SKS.',
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
            'log_aktivitas' => [
                'modul' => 'Admin', 'halaman' => 'Dashboard Admin', 'jenis' => 'kebijakan',
                'judul' => 'Apa yang dicatat',
                'isi' => [
                    'Login (web & aplikasi, termasuk mode penyamaran admin), perubahan data mahasiswa, dosen, tendik, mata kuliah, jadwal, kurikulum, program studi, tahun akademik, kalender, sesi EDOM, gelombang PMB, pengaturan, akun & peran, serta keputusan KRS (disetujui / ditolak / dibuka kembali).',
                    'Setiap perubahan menyimpan nilai lama dan baru (kata sandi tidak pernah dicatat). Impor massal dicatat sebagai satu ringkasan.',
                    'Modul Keuangan dan Perpustakaan tidak dicatat di sini karena terkunci dari Super Admin.',
                    'Log disimpan 1 tahun lalu dihapus otomatis.',
                ],
                'dasar' => 'Kebijakan kampus (jejak audit).',
            ],
            'kalender_pengendali' => [
                'modul' => 'Kalender', 'halaman' => 'Kalender Akademik (Admin)', 'jenis' => 'kebijakan',
                'judul' => 'Kalender sebagai panel kendali',
                'isi' => [
                    'Acara berjenis Periode KRS / EDOM / Gelombang PMB otomatis mengisi tanggal di Tahun Akademik, Sesi EDOM, dan Gelombang PMB; tanggal di form tersebut terkunci dan diubah dari kalender.',
                    'Periode validasi KRS: Dosen Wali / Kaprodi hanya bisa menyetujui, menolak, atau membuka kembali KRS di rentang ini. Periode input nilai: dosen hanya bisa menyimpan nilai di rentang ini. Admin selalu bisa.',
                    'Tanggal selesai ikut dihitung sampai 23.59 WIT. Perpanjangan cukup dengan menggeser tanggal selesai.',
                    'Saklar "Status KRS = tutup" di Pengaturan tetap menutup KRS kapan pun (tutup darurat).',
                    'Bila suatu jenis tidak dijadwalkan di kalender, aturan lama yang berlaku.',
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
                'dasar' => 'SN Dikti (Permendikbudristek 53/2023 Pasal 28) menetapkan bobot A=4, B=3, C=2, D=1, E=0 dan membolehkan huruf antara (AB, BC, dst.). Instrumen akreditasi tidak mengatur batas angka ke huruf; yang dinilai rata-rata IPK lulusan. Batas dan bobot komponen adalah kebijakan kampus.',
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
                'dasar' => 'Rumus IPK mengikuti SN Dikti (Pasal 28); syarat lulus sarjana IPK ≥ 2,00 (Pasal 30). Nilai terbaik untuk MK ulang dan nilai D dihitung lulus adalah kebijakan kampus (tidak diatur instrumen akreditasi).',
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
                    'Akreditasi (EWMP dosen tetap): skor 4 bila 12–16 SKS; 6–12 SKS skor = (2 × SKS − 12) ÷ 3; 16–18 SKS skor = 36 − 2 × SKS; di bawah 6 atau di atas 18 skor 0.',
                    'Komposisi minimal per unsur belum diperiksa sistem.',
                ],
                'dasar' => 'UU 14/2005 Pasal 72, PP 37/2009, PO BKD (Kepdirjen Dikti 12/E/KPT/2021), Permendiktisaintek 52/2025, serta matriks BAN-PT IAPS 4.0 / LAMDIK (butir EWMP/BKD). Bagi dosen tetap yayasan/gereja, rincian per kegiatan mengikuti rubrik BKD kampus.',
            ],
            'bkd_pendidikan' => [
                'modul' => 'BKD', 'halaman' => 'Tridharma & BKD', 'jenis' => 'konfirmasi',
                'judul' => 'Unsur Pendidikan',
                'isi' => [
                    'Pengajaran (ditetapkan kampus): SKS mata kuliah semester aktif × jumlah kelas paralel (rombel) pada jadwal. Setiap dosen team teaching mendapat SKS penuh.',
                    'Perwalian (belum dikonfirmasi): 0,1 SKS per mahasiswa wali aktif, paling banyak 2 SKS.',
                ],
                'dasar' => 'Pengajaran: kebijakan kampus. Perwalian: tidak diatur instrumen akreditasi (yang dinilai hanya total EWMP); nilainya mengikuti rubrik BKD kampus. Contoh rubrik PO BKD di PT lain: pembimbing akademik 2 SKS per semester.',
            ],
            'bkd_tridharma' => [
                'modul' => 'BKD', 'halaman' => 'Tridharma & BKD', 'jenis' => 'konfirmasi',
                'judul' => 'Penelitian, Pengabdian & Penunjang',
                'isi' => [
                    'SKS diisi sendiri oleh dosen per kegiatan (0–12 SKS) dan dihitung bila tahun & semesternya sama dengan semester aktif.',
                    'SK kepanitiaan berstatus Selesai yang terbit pada semester aktif (Ganjil: Agustus–Januari, Genap: Februari–Juli) menambah penunjang 0,5 SKS per SK, paling banyak 3 SKS. Ketua dan anggota belum dibedakan.',
                ],
                'dasar' => 'Tidak diatur instrumen akreditasi (yang dinilai total EWMP). Nilai per kegiatan mengikuti rubrik BKD kampus (contoh rubrik PT lain: kepanitiaan 0,25–0,75 SKS sesuai tingkat & jabatan). Belum ada verifikasi asesor di sistem.',
            ],
            'bkd_kum' => [
                'modul' => 'BKD', 'halaman' => 'Tridharma & BKD', 'jenis' => 'kebijakan',
                'judul' => 'Estimasi angka kredit (KUM)',
                'isi' => [
                    'KUM = angka kredit yang diisi dosen + (SKS pendidikan × 1,5) + (jumlah SK × 1).',
                    'Ini hanya ESTIMASI internal, bukan angka kredit resmi jabatan fungsional.',
                ],
                'dasar' => 'Ditampilkan atas keputusan kampus sebagai alat pantau. Angka kredit resmi tetap ditetapkan melalui Penilaian Angka Kredit (PAK) oleh tim penilai.',
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
                'modul' => 'Akreditasi', 'halaman' => 'Dashboard Penjaminan Mutu, Rektorat & Laporan Mutu', 'jenis' => 'peraturan',
                'judul' => 'Rasio dosen tetap : mahasiswa',
                'isi' => [
                    'Ditampilkan sebagai 1 : n, n = jumlah mahasiswa aktif ÷ jumlah dosen tetap (DTPS).',
                    'Per program studi: DTPS = dosen berstatus Dosen Tetap dengan homebase prodi tersebut. Tingkat institusi: seluruh dosen tetap.',
                    'Rumpun Sosial Humaniora (Teologi, PAK): skor 4 bila 1 : 25 sampai 1 : 35; di bawah 25 skor = 4 × n ÷ 25; 35–50 skor = (200 − 4n) ÷ 15; di atas 50 skor 0.',
                ],
                'dasar' => 'Matriks penilaian BAN-PT IAPS 4.0 Program Sarjana (butir rasio mahasiswa/DTPS) dan LAMDIK Buku 4 Program Sarjana (elemen 11) — rumus sama.',
            ],
            'akreditasi_dosen' => [
                'modul' => 'Akreditasi', 'halaman' => 'Dashboard Penjaminan Mutu', 'jenis' => 'peraturan',
                'judul' => 'Indikator dosen program studi',
                'isi' => [
                    'Jumlah DTPS: skor 4 bila ≥ 12 orang; 3–11 orang skor = (2n + 12) ÷ 9; di bawah 3 skor 0.',
                    'Dosen tidak tetap = dosen non-tetap yang ber-homebase atau mengampu MK kurikulum aktif prodi. Skor 4 bila ≤ 10%; 10–40% skor = (14 − 20p) ÷ 3; di atas 40% skor 0.',
                    'Bimbingan TA: rata-rata mahasiswa bimbingan aktif per pembimbing utama. Skor 4 bila ≤ 6; 6–10 skor = 7 − n ÷ 2; di atas 10 skor 0.',
                    'Dosen dihitung "tetap" dari kolom Status Kepegawaian = Dosen Tetap.',
                ],
                'dasar' => 'Matriks penilaian BAN-PT IAPS 4.0 Program Sarjana dan LAMDIK Buku 4 (dosen tetap, dosen tidak tetap, pembimbing utama). Program studi Teologi dinilai BAN-PT; Pendidikan Agama Kristen dinilai LAMDIK.',
            ],
            'akreditasi_lulusan' => [
                'modul' => 'Akreditasi', 'halaman' => 'Dashboard Penjaminan Mutu', 'jenis' => 'peraturan',
                'judul' => 'Indikator lulusan',
                'isi' => [
                    'Rata-rata IPK lulusan 3 tahun terakhir: skor 4 bila ≥ 3,25; 2,00–3,25 skor = (8 × IPK − 6) ÷ 5.',
                    'Masa studi dihitung dari 1 September tahun masuk sampai tanggal lulus. Tepat waktu = lulus ≤ 4 tahun; skor 4 bila ≥ 50% lulusan tepat waktu, di bawahnya 1 + 6 × persentase.',
                    'Kartu "Rata-rata IPK Mhs Aktif" = rata-rata IPK seluruh mahasiswa aktif yang sudah memiliki nilai (termasuk IPK 0,00).',
                ],
                'dasar' => 'Matriks penilaian BAN-PT IAPS 4.0 Program Sarjana (IPK lulusan, kelulusan tepat waktu) dan LAMDIK Buku 4 (elemen 40).',
            ],
            'tracer' => [
                'modul' => 'Akreditasi', 'halaman' => 'Rekap Tracer Study & Dashboard Penjaminan Mutu', 'jenis' => 'peraturan',
                'judul' => 'Indikator tracer study',
                'isi' => [
                    'Waktu tunggu = rata-rata bulan sampai pekerjaan/pelayanan pertama. Skor 4 bila < 6 bulan; 6–18 bulan skor = (18 − WT) ÷ 3; di atas 18 bulan skor 0.',
                    'Kesesuaian bidang = persentase "Sangat Sesuai" + "Sesuai". Skor 4 bila ≥ 60%; di bawahnya skor = 20 × p ÷ 3.',
                    'Responden minimal: 30% bila lulusan 3 tahun ≥ 300 orang; selain itu 50% − (jumlah lulusan ÷ 300 × 20%). Bila kurang, skor dikalikan (responden ÷ minimal).',
                ],
                'dasar' => 'Matriks penilaian BAN-PT IAPS 4.0 Program Sarjana (waktu tunggu, kesesuaian bidang kerja) dan LAMDIK Buku 4 (elemen 46–47).',
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
