<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotKnowledge;
use Illuminate\Support\Facades\DB;

class ChatbotKnowledgeSeeder extends Seeder
{
    /**
     * Jalankan proses seeding untuk mengisi basis pengetahuan Zoe.
     */
    public function run(): void
    {
        // Bersihkan tabel terlebih dahulu agar tidak terjadi duplikasi data saat di-run ulang
        DB::table('chatbot_knowledges')->truncate();

        $knowledges = [
            [
                'keywords' => 'alur pmb, cara daftar pmb, daftar kuliah, mahasiswa baru, pendaftaran stt',
                'jawaban' => "Untuk mendaftar sebagai mahasiswa baru di STT GPI Papua, silakan ikuti alur berikut:\n" .
                             "1. Buat akun pendaftaran melalui halaman **Register PMB**.\n" .
                             "2. Login ke sistem dan buka menu **Pembayaran**. Lakukan transfer biaya pendaftaran formulir ke rekening resmi yang tertera, lalu unggah bukti bayarnya.\n" .
                             "3. Tunggu hingga Admin Keuangan memverifikasi pembayaran Anda menjadi **LUNAS**.\n" .
                             "4. Setelah lunas, menu **Biodata** akan otomatis terbuka. Lengkapi seluruh kolom isian dan unggah berkas kelengkapan (Ijazah, KK, Pas Foto).\n" .
                             "5. Terakhir, pantau terus status pendaftaran Anda di dashboard hingga proses verifikasi berkas disetujui oleh panitia."
            ],
            [
                'keywords' => 'cara isi krs, kartu rencana studi, alur krs, pilih matkul, rencana studi',
                'jawaban' => "Berikut adalah panduan pengisian KRS Online:\n" .
                             "1. Pastikan Anda telah melunasi tagihan registrasi semester aktif (Sistem akan melakukan pengecekan otomatis).\n" .
                             "2. Buka menu **Akademik > Rencana Studi (KRS)**.\n" .
                             "3. Pilih mata kuliah yang tersedia untuk semester Anda. Perhatikan batas maksimal SKS yang diizinkan berdasarkan IPK Anda sebelumnya (IPK >= 3.00 max 24 SKS, >= 2.50 max 21 SKS, dst.), serta pastikan Anda telah lulus mata kuliah prasyarat (jika ada).\n" .
                             "4. Sistem akan menolak pilihan jika terdeteksi jadwal yang bentrok.\n" .
                             "5. Setelah selesai memilih, simpan pengajuan agar statusnya berubah menjadi **Menunggu Persetujuan**. Silakan hubungi Dosen Wali Anda untuk proses validasi."
            ],
            [
                'keywords' => 'edom, kuesioner dosen, cara isi edom, evaluasi dosen, kuisioner',
                'jawaban' => "Pengisian Kuesioner Evaluasi Dosen (EDOM) adalah tahapan **wajib** bagi seluruh mahasiswa pada setiap akhir semester. Caranya:\n" .
                             "1. Buka menu **Akademik > Evaluasi Dosen (EDOM)**.\n" .
                             "2. Sistem akan menampilkan daftar mata kuliah yang Anda ambil pada semester tersebut beserta nama dosen pengampunya.\n" .
                             "3. Klik tombol **Isi Kuesioner** pada masing-masing mata kuliah.\n" .
                             "4. Berikan penilaian secara objektif pada setiap butir pertanyaan, lalu klik **Simpan**.\n" .
                             "5. Setelah seluruh mata kuliah selesai dievaluasi, barulah sistem akan membuka kunci akses menuju halaman KHS dan Transkrip Nilai Anda."
            ],
            [
                'keywords' => 'tidak bisa lihat khs, khs terkunci, transkrip diblokir, nilai error, buka khs',
                'jawaban' => "Jika Anda tidak bisa mengakses halaman Kartu Hasil Studi (KHS) atau Transkrip Nilai dan dialihkan kembali ke halaman lain, hal tersebut dikarenakan **Anda belum menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM)** untuk semester aktif saat ini.\n\n" .
                             "Silakan masuk ke menu **Akademik > Evaluasi Dosen** dan pastikan status kuesioner pada seluruh mata kuliah Anda di semester ini sudah berstatus *Selesai*. Setelah itu, KHS dan Transkrip Nilai akan langsung dapat diakses secara normal."
            ],
            [
                'keywords' => 'cara bayar kuliah, upload bukti bayar, tagihan spp, cara bayar spp, bukti transfer',
                'jawaban' => "Alur pembayaran tagihan akademik di SIAKAD STT GPI Papua:\n" .
                             "1. Masuk ke dashboard dan pilih menu **Keuangan > Riwayat Pembayaran**.\n" .
                             "2. Anda akan melihat rincian tagihan semester aktif (SPP, formulir, dll.) yang berstatus *Belum Lunas*.\n" .
                             "3. Lakukan transfer sesuai nominal tagihan ke rekening resmi institusi.\n" .
                             "4. Klik tombol **Upload Bukti** pada tagihan yang bersangkutan, pilih file foto/screenshot bukti transfer, lalu kirim.\n" .
                             "5. Status tagihan akan berubah menjadi *Menunggu Konfirmasi*. Bagian Keuangan akan memverifikasi dan mengubah statusnya menjadi **LUNAS**."
            ],
            [
                'keywords' => 'verum, cara masuk kelas online, e-learning, gabung meeting, materi kuliah, tugas online',
                'jawaban' => "Panduan mengakses kelas digital (Verum) bagi mahasiswa:\n" .
                             "1. Pastikan mata kuliah yang bersangkutan telah resmi disetujui di dalam KRS Anda.\n" .
                             "2. Buka menu **E-Learning (Verum)** di dashboard.\n" .
                             "3. Pilih kelas mata kuliah yang ingin Anda ikuti. Di dalamnya Anda bisa mengunduh materi ajar, berdiskusi di forum, atau mengumpulkan tugas.\n" .
                             "4. Jika dosen telah memulai sesi perkuliahan tatap muka online, tombol **Gabung Meeting / Kelas Online** akan menyala dan dapat Anda klik untuk langsung bergabung ke dalam ruang virtual."
            ]
        ];

        foreach ($knowledges as $data) {
            ChatbotKnowledge::create($data);
        }
    }
}