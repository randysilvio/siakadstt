<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotKnowledge;
use Illuminate\Support\Facades\DB;

class ChatbotKnowledgeSeeder extends Seeder
{
    /**
     * Jalankan proses seeding untuk mengisi basis pengetahuan Zoe secara menyeluruh (100% Alur SIAKAD).
     */
    public function run(): void
    {
        // Bersihkan tabel terlebih dahulu agar tidak terjadi duplikasi data saat di-run ulang
        DB::table('chatbot_knowledges')->truncate();

        $knowledges = [
            // =================================================================================
            // 1. ALUR CAMABA & PENDAFTARAN (PMB)
            // =================================================================================
            [
                'keywords' => 'alur pmb, cara daftar pmb, daftar kuliah, mahasiswa baru, pendaftaran stt, bayar formulir pmb',
                'jawaban' => "Untuk mendaftar sebagai mahasiswa baru di STT GPI Papua, silakan ikuti alur berikut:\n" .
                             "1. Buat akun pendaftaran melalui halaman **Register PMB**.\n" .
                             "2. Login ke sistem dan buka menu **Pembayaran**. Lakukan transfer biaya pendaftaran formulir ke rekening resmi yang tertera, lalu unggah bukti bayarnya.\n" .
                             "3. Tunggu hingga Admin Keuangan memverifikasi pembayaran Anda menjadi **LUNAS**.\n" .
                             "4. Setelah lunas, menu **Biodata** akan otomatis terbuka. Lengkapi seluruh kolom isian dan unggah berkas kelengkapan (Ijazah, KK, Pas Foto).\n" .
                             "5. Terakhir, pantau terus status pendaftaran Anda di dashboard hingga proses verifikasi berkas disetujui oleh panitia."
            ],

            // =================================================================================
            // 2. ALUR MAHASISWA (KRS, KHS, TRANSKRIP, KEUANGAN, EVALUASI)
            // =================================================================================
            [
                'keywords' => 'cara isi krs, kartu rencana studi, alur krs, pilih matkul, rencana studi, batas sks',
                'jawaban' => "Berikut adalah panduan pengisian KRS Online:\n" .
                             "1. Pastikan Anda telah melunasi tagihan registrasi semester aktif (Sistem akan melakukan pengecekan otomatis).\n" .
                             "2. Buka menu **Akademik > Rencana Studi (KRS)**.\n" .
                             "3. Pilih mata kuliah yang tersedia untuk semester Anda. Perhatikan batas maksimal SKS yang diizinkan berdasarkan IPK Anda sebelumnya (IPK >= 3.00 max 24 SKS, >= 2.50 max 21 SKS, dst.), serta pastikan Anda telah lulus mata kuliah prasyarat (jika ada).\n" .
                             "4. Sistem akan menolak pilihan jika terdeteksi jadwal yang bentrok.\n" .
                             "5. Setelah selesai memilih, simpan pengajuan agar statusnya berubah menjadi **Menunggu Persetujuan**. Silakan hubungi Dosen Wali Anda untuk proses validasi."
            ],
            [
                'keywords' => 'edom, kuesioner dosen, cara isi edom, evaluasi dosen, kuisioner, penilaian dosen',
                'jawaban' => "Pengisian Kuesioner Evaluasi Dosen (EDOM) adalah tahapan **wajib** bagi seluruh mahasiswa pada setiap akhir semester. Caranya:\n" .
                             "1. Buka menu **Akademik > Evaluasi Dosen (EDOM)**.\n" .
                             "2. Sistem akan menampilkan daftar mata kuliah yang Anda ambil pada semester tersebut beserta nama dosen pengampunya.\n" .
                             "3. Klik tombol **Isi Kuesioner** pada masing-masing mata kuliah.\n" .
                             "4. Berikan penilaian secara objektif pada setiap butir pertanyaan dengan skala yang tersedia, lalu klik **Simpan**.\n" .
                             "5. Setelah seluruh mata kuliah selesai dievaluasi, barulah sistem akan membuka kunci akses menuju halaman KHS dan Transkrip Nilai Anda."
            ],
            [
                'keywords' => 'tidak bisa lihat khs, khs terkunci, transkrip diblokir, nilai error, buka khs, menu khs hilang',
                'jawaban' => "Jika Anda tidak bisa mengakses halaman Kartu Hasil Studi (KHS) atau Transkrip Nilai dan dialihkan kembali ke halaman lain, hal tersebut dikarenakan **Anda belum menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM)** untuk semester aktif saat ini.\n\n" .
                             "Silakan masuk ke menu **Akademik > Evaluasi Dosen** dan pastikan status kuesioner pada seluruh mata kuliah Anda di semester ini sudah berstatus *Selesai*. Setelah itu, KHS dan Transkrip Nilai akan langsung dapat diakses secara normal."
            ],
            [
                'keywords' => 'cara bayar kuliah, upload bukti bayar, tagihan spp, cara bayar spp, bukti transfer, konfirmasi bayar',
                'jawaban' => "Alur pembayaran tagihan akademik di SIAKAD STT GPI Papua:\n" .
                             "1. Masuk ke dashboard dan pilih menu **Keuangan > Riwayat Pembayaran**.\n" .
                             "2. Anda akan melihat rincian tagihan semester aktif (SPP, formulir, dll.) yang berstatus *Belum Lunas*.\n" .
                             "3. Lakukan transfer sesuai nominal tagihan ke rekening resmi institusi.\n" .
                             "4. Klik tombol **Upload Bukti** pada tagihan yang bersangkutan, pilih file foto/screenshot bukti transfer, lalu kirim.\n" .
                             "5. Status tagihan akan berubah menjadi *Menunggu Konfirmasi*. Bagian Keuangan akan memverifikasi dan mengubah statusnya menjadi **LUNAS**."
            ],
            [
                'keywords' => 'verum, cara masuk kelas online, e-learning, gabung meeting, materi kuliah, tugas online, absen e-learning',
                'jawaban' => "Panduan mengakses kelas digital (Verum) bagi mahasiswa:\n" .
                             "1. Pastikan mata kuliah yang bersangkutan telah resmi disetujui di dalam KRS Anda.\n" .
                             "2. Buka menu **E-Learning (Verum)** di dashboard.\n" .
                             "3. Pilih kelas mata kuliah yang ingin Anda ikuti. Di dalamnya Anda bisa mengunduh materi ajar, berdiskusi di forum, melakukan presensi, atau mengumpulkan tugas.\n" .
                             "4. Jika dosen telah memulai sesi perkuliahan tatap muka online, tombol **Gabung Meeting / Kelas Online** akan menyala dan dapat Anda klik untuk langsung bergabung ke dalam ruang virtual."
            ],

            // =================================================================================
            // 3. ALUR DOSEN (PENGAMPU & WALI)
            // =================================================================================
            [
                'keywords' => 'input nilai, cara isi nilai, nilai mahasiswa, tugas dosen pengampu, rubah nilai',
                'jawaban' => "SOP Pengisian Nilai oleh Dosen Pengampu:\n" .
                             "1. Akses menu **Akademik > Input Nilai** di dashboard Dosen.\n" .
                             "2. Pilih mata kuliah yang Anda ampu pada semester aktif.\n" .
                             "3. Sistem akan menampilkan daftar mahasiswa yang resmi mengambil mata kuliah tersebut.\n" .
                             "4. Masukkan nilai huruf (A, B, C, D, atau E) pada kolom yang disediakan untuk masing-masing mahasiswa.\n" .
                             "5. Klik tombol **Simpan**. Nilai yang diinput akan langsung terdistribusi ke KHS dan Transkrip mahasiswa yang bersangkutan."
            ],
            [
                'keywords' => 'perwalian, cara validasi krs mahasiswa, acc krs dosen wali, bimbingan krs, tolak krs, hapus krs mahasiswa',
                'jawaban' => "SOP Validasi Perwalian KRS oleh Dosen Wali:\n" .
                             "1. Masuk ke menu **Perwalian** di dashboard Dosen.\n" .
                             "2. Anda akan melihat daftar mahasiswa bimbingan Anda beserta status pengajuan KRS mereka.\n" .
                             "3. Klik nama mahasiswa untuk meninjau detail sebaran mata kuliah dan total SKS yang diambil.\n" .
                             "4. Jika sesuai, ubah status KRS menjadi **Disetujui**. Jika ada kesalahan, Anda dapat menghapus mata kuliah tertentu secara paksa (revisi) atau mengubah statusnya menjadi **Ditolak**.\n" .
                             "5. Anda juga dapat mengklaim mahasiswa baru yang belum memiliki dosen wali melalui daftar ketersediaan mahasiswa."
            ],
            [
                'keywords' => 'kelola verum, buat kelas verum, mulai meeting dosen, e-learning dosen, upload materi dosen',
                'jawaban' => "SOP Pengelolaan Kelas E-Learning (Verum) bagi Dosen:\n" .
                             "1. Buka menu **E-Learning (Verum)**. Anda dapat membuat kelas baru khusus untuk mata kuliah yang Anda ampu di semester aktif.\n" .
                             "2. Di dalam panel kelas, Anda bisa mengunggah materi perkuliahan, membuat penugasan, memposting pengumuman di forum, dan membuka sesi absensi/presensi.\n" .
                             "3. Untuk perkuliahan sinkron, klik tombol **Mulai Meeting** agar akses ruang kelas online terbuka bagi seluruh mahasiswa terdaftar, dan klik **Akhiri Meeting** setelah sesi tatap muka selesai."
            ],

            // =================================================================================
            // 4. ALUR KAPRODI
            // =================================================================================
            [
                'keywords' => 'kaprodi, cara validasi krs kaprodi, tugas kaprodi, acc krs akhir, persetujuan kaprodi',
                'jawaban' => "SOP Validasi KRS Tingkat Akhir oleh Ketua Program Studi (Kaprodi):\n" .
                             "1. Setelah mahasiswa mendapatkan persetujuan KRS dari Dosen Wali, pengajuan akan diteruskan ke antarmuka **Dashboard Kaprodi**.\n" .
                             "2. Kaprodi meninjau kesesuaian sebaran mata kuliah khusus untuk mahasiswa di program studinya.\n" .
                             "3. Kaprodi memberikan status final **Disetujui** atau **Ditolak** disertai catatan peninjauan jika diperlukan.\n" .
                             "4. Validasi ini memicu pengiriman notifikasi otomatis kepada mahasiswa terkait hasil akhir persetujuan KRS mereka."
            ],

            // =================================================================================
            // 5. ALUR ADMIN KEUANGAN
            // =================================================================================
            [
                'keywords' => 'admin keuangan, verifikasi pembayaran, buat tagihan massal, generate tagihan, laporan keuangan, validasi lunas',
                'jawaban' => "SOP Pengelolaan Keuangan oleh Admin Keuangan:\n" .
                             "1. **Verifikasi Bayar:** Memantau tagihan dengan status *Menunggu Konfirmasi*, memeriksa kesesuaian bukti transfer, lalu mengklik aksi **Tandai Lunas**.\n" .
                             "2. **Generate Massal:** Membuat tagihan serentak (SPP, registrasi, dll.) berdasarkan kriteria Prodi dan Angkatan melalui menu **Generate Tagihan** untuk menghindari input manual satu per satu.\n" .
                             "3. **Tagihan Manual:** Membuat tagihan spesifik untuk satu mahasiswa jika ada keperluan insidental.\n" .
                             "4. **Laporan:** Memfilter riwayat transaksi dan mengekspor/mencetak Laporan Keuangan dalam format PDF resmi."
            ],

            // =================================================================================
            // 6. ALUR PUSTAKAWAN
            // =================================================================================
            [
                'keywords' => 'alur perpustakaan, pinjam buku, pustakawan, kembalikan buku, denda buku, kelola koleksi, sirkulasi perpustakaan',
                'jawaban' => "SOP Layanan Sirkulasi & Pengelolaan Perpustakaan:\n" .
                             "1. **Koleksi:** Pustakawan menginput data buku baru (Judul, Pengarang, ISBN, Stok). Jumlah ketersediaan buku akan otomatis disesuaikan dengan total stok.\n" .
                             "2. **Peminjaman:** Pustakawan mencatat transaksi peminjaman di sistem saat mahasiswa meminjam buku, yang otomatis mengurangi stok ketersediaan di rak.\n" .
                             "3. **Monitoring:** Pustakawan memantau peminjaman aktif serta daftar keterlambatan melalui Dashboard Perpustakaan.\n" .
                             "4. **Pengembalian:** Memproses pengembalian buku di sistem agar stok kembali bertambah, serta mencatat sanksi/denda jika peminjaman melewati tanggal jatuh tempo."
            ],

            // =================================================================================
            // 7. ALUR TENDIK & ADMIN AKADEMIK
            // =================================================================================
            [
                'keywords' => 'tugas tendik, admin absensi, rekap kehadiran, dokumen publik, kalender akademik, kelola pengumuman, broadcast notifikasi',
                'jawaban' => "SOP Layanan Operasional Tenaga Kependidikan (Tendik) & Admin:\n" .
                             "1. **Manajemen Absensi:** Tendik mengelola pengaturan titik lokasi presensi dan menarik laporan rekapitulasi kehadiran mahasiswa/dosen untuk keperluan evaluasi institusi.\n" .
                             "2. **Distribusi Informasi:** Membuat dan mengelola **Pengumuman/Berita** serta mengunggah berkas di **Dokumen Publik**. Informasi penting dapat didistribusikan langsung ke seluruh pengguna atau spesifik per role melalui sistem *Broadcast Notification*.\n" .
                             "3. **Agenda Kampus:** Memperbarui **Kalender Akademik** secara berkala agar seluruh civitas akademika mendapatkan notifikasi real-time terkait jadwal perkuliahan, masa ujian, dan hari libur."
            ]
        ];

        // Masukkan seluruh array data SOP di atas ke dalam database
        foreach ($knowledges as $data) {
            ChatbotKnowledge::create($data);
        }
    }
}