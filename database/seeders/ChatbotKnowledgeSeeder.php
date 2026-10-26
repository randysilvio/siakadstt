<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotKnowledge;
use Illuminate\Support\Facades\DB;

class ChatbotKnowledgeSeeder extends Seeder
{
    /**
     * Mengisi basis pengetahuan (Knowledge Base) Zoe Chatbot untuk seluruh 22 Modul SIAKAD.
     */
    public function run(): void
    {
        DB::table('chatbot_knowledges')->truncate();

        $knowledges = [
            // 1. SAPAAN & UMUM
            [
                'keywords' => 'halo, hai, shalom, syalom, selamat pagi, selamat siang, selamat sore, selamat malam, pace, mace, kaka, zoe, siapa kamu',
                'jawaban' => "Shalom! Saya **Zoe**, Asisten Virtual Pintar SIAKAD. Saya siap membantu Anda terkait informasi **PMB Online, Pengisian KRS Rombel A/B, KHS & Transkrip, E-Learning Verum, Keuangan & Dispensasi Cicilan, Bimbingan Skripsi/PPL, Permohonan Surat QR-Code, Perpustakaan, hingga Portofolio BKD Dosen**."
            ],

            // 2. MODUL PMB (CALON MAHASISWA BARU)
            [
                'keywords' => 'alur pmb, cara daftar pmb, daftar kuliah, mahasiswa baru, camaba, kartu ujian pmb',
                'jawaban' => "[PANDUAN PMB ONLINE]\n" .
                             "1. Klik **Daftar PMB** di halaman depan website untuk membuat akun Camaba.\n" .
                             "2. Login ke Dashboard Camaba, buka menu **Pembayaran**, lalu unggah bukti transfer biaya formulir pendaftaran.\n" .
                             "3. Setelah diverifikasi **LUNAS** oleh Bagian Keuangan, menu **Biodata PMB** akan terbuka.\n" .
                             "4. Lengkapi biodata, pilih Program Studi 1 & 2, serta unggah berkas (Ijazah, KK, Pas Foto).\n" .
                             "5. Klik **Cetak Kartu Ujian (PDF)** untuk dibawa saat seleksi. Jika dinyatakan **Lulus**, akun Anda otomatis menjadi Mahasiswa Aktif lengkap dengan NIM baru!"
            ],

            // 3. MODUL KRS, ROMBEL A/B, & KUOTA SKS
            [
                'keywords' => 'cara isi krs, kartu rencana studi, alur krs, pilih matkul, batas sks, kelas a, kelas b, rombel, jadwal bentrok, prasyarat',
                'jawaban' => "[PANDUAN KRS & KELAS PARALEL A/B]\n" .
                             "1. Pastikan tagihan SPP semester aktif sudah **Lunas** atau telah mendapat status **Dispensasi Cicilan** dari Bagian Keuangan, serta sudah mengisi **Evaluasi Dosen (EDOM)**.\n" .
                             "2. Buka menu **Akademik > Kartu Rencana Studi (KRS)**.\n" .
                             "3. Sistem otomatis menghitung batas maksimal SKS Anda berdasarkan IPK (IPK >= 3.00 maks 24 SKS; 2.50–2.99 maks 21 SKS; 2.00–2.49 maks 18 SKS; < 2.00 maks 15 SKS; Mahasiswa Baru otomatis 24 SKS).\n" .
                             "4. Centang mata kuliah dan pilih **Kelas Paralel (Kelas A / B)** yang masih memiliki sisa kuota kursi. Sistem otomatis mencegah jadwal bentrok dan memeriksa kelulusan mata kuliah prasyarat.\n" .
                             "5. Klik **Simpan Rencana Studi**, lalu tunggu persetujuan Dosen Pembimbing/Tutor atau Kaprodi."
            ],

            // 4. MODUL KEUANGAN & DISPENSASI CICILAN KRS
            [
                'keywords' => 'cara bayar, bayar spp, upload bukti, dispensasi, cicilan, krs terkunci pembayaran, belum lunas, rekening kampus',
                'jawaban' => "[PANDUAN KEUANGAN & DISPENSASI CICILAN KRS]\n" .
                             "1. Buka menu **Akademik > Riwayat Pembayaran** untuk melihat tagihan semester aktif.\n" .
                             "2. Transfer sesuai nominal ke rekening resmi kampus yang tertera di layar, lalu klik **Upload Bukti** (format JPG/PNG maks 2MB).\n" .
                             "3. Jika Anda mencicil biaya kuliah dan sudah mendapat izin resmi, Bagian Keuangan dapat mengubah status tagihan Anda menjadi **DISPENSASI CICILAN** sehingga gembok pengisian KRS Anda langsung terbuka!"
            ],

            // 5. MODUL EDOM, KHS, TRANSKRIP & TRACER STUDY ALUMNI
            [
                'keywords' => 'edom, evaluasi dosen, kuesioner dosen, tidak bisa cetak khs, transkrip terkunci, tracer study, alumni',
                'jawaban' => "[ATURAN CETAK KHS, TRANSKRIP & EDOM]\n" .
                             "1. **Mahasiswa Aktif:** Wajib menyelesaikan kuesioner **Evaluasi Dosen (EDOM)** pada menu *Akademik > Evaluasi Dosen* untuk seluruh mata kuliah yang memiliki dosen pengampu. Setelah terisi semua, tombol **Cetak KHS (PDF)** dan **Cetak Transkrip (PDF)** otomatis terbuka.\n" .
                             "2. **Mahasiswa Lulus (Alumni):** Wajib mengisi kuesioner pelacakan lulusan pada menu **Alumni > Tracer Study Alumni** satu kali sebelum dapat mengunduh PDF Transkrip Nilai Akhir."
            ],

            // 6. MODUL E-LEARNING VERUM & BAP
            [
                'keywords' => 'verum, e-learning, kelas online, kuliah daring, presensi online, absen hadir, kumpul tugas, bap dosen',
                'jawaban' => "[PANDUAN E-LEARNING VERUM]\n" .
                             "1. **Bagi Mahasiswa:** Buka menu **Verum**, pilih kelas mata kuliah sesuai KRS Anda. Di dalamnya Anda dapat bergabung ke *Video Conference Live*, klik tombol **Hadir** pada tab *Presensi*, mengunduh bahan ajar di tab *Materi*, dan mengunggah tugas di tab *Tugas*.\n" .
                             "2. **Bagi Dosen:** Dosen dapat membuat ruang kelas Verum, membuka sesi presensi pertemuan 1 s/d 16 sambil mengisi **Jurnal Perkuliahan (Pokok Bahasan & Realisasi Materi)**, menilai tugas mahasiswa, serta mencetak **Rekap Presensi** dan **Berita Acara Perkuliahan (BAP) PDF**."
            ],

            // 7. MODUL BUKU NILAI OBE & SINKRONISASI VERUM
            [
                'keywords' => 'input nilai, buku nilai, nilai obe, tarik data verum, komponen nilai, bobot nilai, nilai uts uas',
                'jawaban' => "[PANDUAN BUKU NILAI OBE DOSEN]\n" .
                             "1. Buka **Ruang Kerja Dosen** lalu klik **Input Nilai** pada mata kuliah yang diampu.\n" .
                             "2. Klik tombol **Tarik Data Verum** di kanan atas untuk menarik otomatis persentase kehadiran dan rata-rata nilai tugas mahasiswa dari kelas Verum.\n" .
                             "3. Isi nilai **UTS** dan **UAS** (skala 0–100). Sistem otomatis menghitung Nilai Akhir Angka dan Nilai Huruf (A/B/C/D/E) secara langsung di layar."
            ],

            // 8. MODUL BIMBINGAN SKRIPSI / TUGAS AKHIR & PPL
            [
                'keywords' => 'skripsi, tugas akhir, ppl, vikariat, bimbingan skripsi, logbook bimbingan, kartu bimbingan, dosen pembimbing',
                'jawaban' => "[PANDUAN BIMBINGAN SKRIPSI & PPL]\n" .
                             "1. Buka menu **Akademik > Bimbingan Skripsi & PPL**.\n" .
                             "2. Mahasiswa mengajukan judul Skripsi/Tugas Akhir atau lokasi PPL. Kaprodi akan menetapkan Dosen Pembimbing 1 & 2.\n" .
                             "3. Setiap selesai konsultasi, mahasiswa mencatat kemajuan dan mengunggah draf bab di **Logbook Konsultasi** untuk diparaf secara elektronik (*ACC / Revisi*) oleh Dosen Pembimbing.\n" .
                             "4. Klik **Cetak Kartu Bimbingan (PDF)** untuk mencetak lembar kendali resmi."
            ],

            // 9. MODUL PERMOHONAN SURAT MANDIRI & VERIFIKASI QR-CODE
            [
                'keywords' => 'surat aktif kuliah, minta surat, permohonan surat, surat pengantar penelitian, surat beasiswa, verifikasi qr, cek keaslian dokumen',
                'jawaban' => "[LAYANAN SURAT MANDIRI & VALIDASI QR-CODE]\n" .
                             "1. Mahasiswa dapat mengajukan permohonan surat resmi melalui menu **Akademik > Permohonan Surat** (pilih jenis surat: *Surat Keterangan Aktif Kuliah, Pengantar Penelitian, Pengantar Magang/PPL, atau Rekomendasi Beasiswa*).\n" .
                             "2. Setelah diverifikasi oleh BAAK menjadi **Disetujui**, tombol **Cetak Surat (PDF)** langsung muncul di akun mahasiswa.\n" .
                             "3. Seluruh dokumen PDF (KRS, KHS, Transkrip, Surat Aktif, E-KTM) dilengkapi **Segel Validasi Elektronik & Kode Hash HMAC-SHA256** yang dapat dicek keasliannya secara daring."
            ],

            // 10. MODUL TRIDHARMA DOSEN, BKD, & NEO FEEDER PDDIKTI
            [
                'keywords' => 'tridharma, bkd dosen, beban kerja dosen, penelitian dosen, pengabdian masyarakat, sister, feeder pddikti, backup database',
                'jawaban' => "[PORTOFOLIO TRIDHARMA BKD & SINKRONISASI FEEDER]\n" .
                             "1. **Dosen:** Dapat mencatat kegiatan Penelitian, Pengabdian (PkM), dan Penunjang di menu **Akademik & Dosen > Data Tridharma**, mengunggah bukti PDF, serta mencetak **Laporan BKD & CV Akademik (PDF)**.\n" .
                             "2. **Operator PDDIKTI / Admin:** Dapat mengunduh 5 paket CSV standar Importer Neo Feeder (*Biodata Mahasiswa, Kelas Kuliah, KRS & Nilai, AKM, Ajar Dosen*) serta melakukan **1-Click Full SQL Database Backup** melalui menu **Pengaturan > Sinkronisasi Feeder & Backup**."
            ],
        ];

        foreach ($knowledges as $data) {
            ChatbotKnowledge::create($data);
        }
    }
}