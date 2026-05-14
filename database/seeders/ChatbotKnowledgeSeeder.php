<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotKnowledge;
use Illuminate\Support\Facades\DB;

class ChatbotKnowledgeSeeder extends Seeder
{
    /**
     * Jalankan proses seeding untuk mengisi basis pengetahuan Zoe secara terstruktur (Dipisah per Role).
     */
    public function run(): void
    {
        // Bersihkan tabel terlebih dahulu agar tidak terjadi duplikasi data saat di-run ulang
        DB::table('chatbot_knowledges')->truncate();

        $knowledges = [
            // =================================================================================
            // 1. ROLE: CAMABA (CALON MAHASISWA BARU)
            // =================================================================================
            [
                'keywords' => 'alur pmb, cara daftar pmb, daftar kuliah, mahasiswa baru, pendaftaran stt',
                'jawaban' => "[ROLE: CAMABA] Untuk mendaftar sebagai mahasiswa baru di STT GPI Papua, silakan ikuti alur berikut:\n" .
                             "1. Buat akun pendaftaran melalui halaman **Register PMB**.\n" .
                             "2. Login ke sistem dan buka menu **Pembayaran** untuk mentransfer biaya formulir.\n" .
                             "3. Tunggu hingga Admin Keuangan memverifikasi pembayaran Anda menjadi **LUNAS**.\n" .
                             "4. Menu **Biodata** akan otomatis terbuka. Lengkapi isian dan unggah berkas (Ijazah, KK, Pas Foto).\n" .
                             "5. Pantau status pendaftaran di dashboard hingga berkas disetujui panitia."
            ],
            [
                'keywords' => 'bayar formulir pmb, cara bayar pmb, bukti bayar pmb, tagihan pmb',
                'jawaban' => "[ROLE: CAMABA] Panduan pembayaran formulir PMB:\n" .
                             "1. Setelah registrasi akun, login dan masuk ke menu **Pembayaran**.\n" .
                             "2. Transfer nominal biaya pendaftaran ke rekening resmi yang tertera di halaman tersebut.\n" .
                             "3. Unggah foto/berkas bukti transfer pada kolom yang disediakan.\n" .
                             "4. Status tagihan akan menjadi *Menunggu Konfirmasi*. Jika sudah diverifikasi Admin Keuangan, status berubah menjadi **LUNAS** dan akses pengisian biodata akan terbuka."
            ],

            // =================================================================================
            // 2. ROLE: MAHASISWA
            // =================================================================================
            [
                'keywords' => 'cara isi krs, kartu rencana studi, alur krs, pilih matkul, rencana studi, batas sks',
                'jawaban' => "[ROLE: MAHASISWA] Panduan pengisian Rencana Studi (KRS Online):\n" .
                             "1. Pastikan tagihan registrasi semester aktif telah lunas.\n" .
                             "2. Buka menu **Akademik > Rencana Studi (KRS)**.\n" .
                             "3. Pilih mata kuliah yang tersedia. Perhatikan batas maksimal SKS yang diizinkan berdasarkan IPK Anda sebelumnya (IPK >= 3.00 max 24 SKS, >= 2.50 max 21 SKS, dst.) dan pastikan lulus matkul prasyarat.\n" .
                             "4. Sistem otomatis menolak jika ada jadwal perkuliahan yang bentrok.\n" .
                             "5. Simpan pengajuan agar statusnya menjadi **Menunggu Persetujuan**, lalu hubungi Dosen Wali Anda."
            ],
            [
                'keywords' => 'edom, kuesioner dosen, cara isi edom, evaluasi dosen, kuisioner, penilaian dosen',
                'jawaban' => "[ROLE: MAHASISWA] Kewajiban pengisian Kuesioner Evaluasi Dosen (EDOM):\n" .
                             "1. Akses menu **Akademik > Evaluasi Dosen (EDOM)** di akhir semester.\n" .
                             "2. Pilih mata kuliah yang ingin dievaluasi dan klik **Isi Kuesioner**.\n" .
                             "3. Jawab seluruh butir pertanyaan secara objektif menggunakan skala 1-4 yang tersedia, lalu klik **Simpan**.\n" .
                             "4. EDOM wajib diisi untuk seluruh mata kuliah aktif Anda agar sistem membuka kunci akses ke KHS dan Transkrip Nilai."
            ],
            [
                'keywords' => 'tidak bisa lihat khs, khs terkunci, transkrip diblokir, nilai error, menu khs hilang',
                'jawaban' => "[ROLE: MAHASISWA] Solusi KHS atau Transkrip Nilai yang terkunci:\n" .
                             "Sistem memblokir akses KHS/Transkrip jika Anda **belum menyelesaikan pengisian EDOM** pada semester aktif saat ini.\n" .
                             "Silakan masuk ke menu **Akademik > Evaluasi Dosen** dan pastikan seluruh mata kuliah Anda di semester ini sudah berstatus *Selesai*. Kunci akses akan otomatis terbuka setelahnya."
            ],
            [
                'keywords' => 'cara bayar kuliah, upload bukti bayar, tagihan spp, cara bayar spp, bukti transfer',
                'jawaban' => "[ROLE: MAHASISWA] Alur pembayaran tagihan SPP/Akademik:\n" .
                             "1. Buka menu **Keuangan > Riwayat Pembayaran**.\n" .
                             "2. Pilih tagihan semester aktif yang berstatus *Belum Lunas*.\n" .
                             "3. Transfer nominal tagihan ke rekening kampus, lalu klik tombol **Upload Bukti** pada tagihan tersebut.\n" .
                             "4. Setelah bukti terkirim, status menjadi *Menunggu Konfirmasi*. Akses akademik akan normal kembali setelah Admin Keuangan memverifikasinya menjadi **LUNAS**."
            ],
            [
                'keywords' => 'verum mahasiswa, cara masuk kelas online, e-learning, gabung meeting, materi kuliah, tugas online',
                'jawaban' => "[ROLE: MAHASISWA] Akses ruang perkuliahan digital (E-Learning Verum):\n" .
                             "1. Pastikan KRS Anda sudah berstatus *Disetujui*.\n" .
                             "2. Buka menu **E-Learning (Verum)** dan pilih kelas mata kuliah Anda.\n" .
                             "3. Anda dapat mengunduh materi ajar, mengumpulkan tugas, atau mengisi daftar hadir (presensi).\n" .
                             "4. Jika dosen telah membuka ruang virtual, tombol **Gabung Meeting / Kelas Online** akan aktif dan dapat diklik untuk tatap muka sinkron."
            ],

            // =================================================================================
            // 3. ROLE: DOSEN PENGAMPU
            // =================================================================================
            [
                'keywords' => 'input nilai, cara isi nilai, nilai mahasiswa, tugas dosen pengampu, rubah nilai',
                'jawaban' => "[ROLE: DOSEN PENGAMPU] Panduan pengisian nilai akhir mahasiswa:\n" .
                             "1. Masuk ke menu **Akademik > Input Nilai**.\n" .
                             "2. Pilih mata kuliah yang Anda ampu pada semester aktif untuk melihat daftar mahasiswa terdaftar.\n" .
                             "3. Masukkan nilai huruf (A, B, C, D, atau E) pada kolom yang tersedia.\n" .
                             "4. Klik **Simpan**. Nilai akan langsung masuk secara real-time ke dalam KHS dan Transkrip mahasiswa bersangkutan."
            ],
            [
                'keywords' => 'kelola verum, buat kelas verum, mulai meeting dosen, e-learning dosen, upload materi dosen',
                'jawaban' => "[ROLE: DOSEN PENGAMPU] Manajemen modul E-Learning (Verum):\n" .
                             "1. Buka menu **E-Learning (Verum)** dan buat kelas baru untuk mata kuliah yang Anda ampu.\n" .
                             "2. Di dalam panel kelas, Anda dapat mengunggah modul materi, membuat penugasan, atau mengelola presensi.\n" .
                             "3. Untuk memulai kuliah video conference, klik **Mulai Meeting** agar jalur akses mahasiswa terbuka, dan klik **Akhiri Meeting** untuk menutup sesi."
            ],

            // =================================================================================
            // 4. ROLE: DOSEN WALI (PEMBIMBING AKADEMIK)
            // =================================================================================
            [
                'keywords' => 'perwalian, cara validasi krs mahasiswa, acc krs dosen wali, bimbingan krs, tolak krs',
                'jawaban' => "[ROLE: DOSEN WALI] Prosedur perwalian dan validasi KRS:\n" .
                             "1. Buka menu **Perwalian** untuk melihat daftar mahasiswa bimbingan Anda yang mengajukan KRS.\n" .
                             "2. Klik nama mahasiswa untuk meninjau sebaran mata kuliah, jadwal bentrok, dan total SKS.\n" .
                             "3. Jika pengajuan sudah tepat, ubah statusnya menjadi **Disetujui**.\n" .
                             "4. Jika terdapat kekeliruan, Anda dapat menolak KRS atau melakukan revisi dengan menghapus mata kuliah tertentu secara langsung dari sistem."
            ],
            [
                'keywords' => 'klaim mahasiswa perwalian, tambah anak wali, daftar perwalian baru',
                'jawaban' => "[ROLE: DOSEN WALI] Penambahan mahasiswa bimbingan baru:\n" .
                             "Jika ada mahasiswa baru atau mahasiswa aktif yang belum memiliki dosen wali, Anda dapat mengklaimnya melalui menu **Perwalian**. Pilih mahasiswa yang tersedia pada tabel ketersediaan, lalu tambahkan ke dalam daftar bimbingan Anda."
            ],

            // =================================================================================
            // 5. ROLE: KAPRODI (KETUA PROGRAM STUDI)
            // =================================================================================
            [
                'keywords' => 'kaprodi, cara validasi krs kaprodi, tugas kaprodi, acc krs akhir, persetujuan kaprodi',
                'jawaban' => "[ROLE: KAPRODI] Otorisasi persetujuan KRS tingkat akhir:\n" .
                             "1. KRS mahasiswa yang telah disetujui Dosen Wali akan diteruskan ke **Dashboard Kaprodi**.\n" .
                             "2. Kaprodi meninjau kesesuaian kurikulum khusus untuk mahasiswa di program studinya.\n" .
                             "3. Berikan status final **Disetujui** atau **Ditolak** disertai catatan peninjauan jika diperlukan.\n" .
                             "4. Aksi ini memicu pengiriman notifikasi otomatis kepada mahasiswa terkait status final KRS mereka."
            ],

            // =================================================================================
            // 6. ROLE: ADMIN KEUANGAN
            // =================================================================================
            [
                'keywords' => 'admin keuangan, verifikasi pembayaran, validasi lunas, cek bukti bayar',
                'jawaban' => "[ROLE: ADMIN KEUANGAN] Verifikasi pembayaran tagihan mahasiswa:\n" .
                             "1. Buka menu **Pembayaran / Tagihan** di antarmuka administrator.\n" .
                             "2. Filter data berdasarkan status *Menunggu Konfirmasi*.\n" .
                             "3. Periksa foto/dokumen bukti transfer yang diunggah mahasiswa. Jika dana valid, klik aksi **Tandai Lunas** agar sistem membuka blokir akses akademik mahasiswa."
            ],
            [
                'keywords' => 'buat tagihan massal, generate tagihan, tagihan spp otomatis, tagihan semesteran',
                'jawaban' => "[ROLE: ADMIN KEUANGAN] Prosedur pembuatan tagihan massal (Generate Tagihan):\n" .
                             "Untuk efisiensi, hindari pembuatan tagihan manual satu per satu. Gunakan menu **Generate Tagihan**, pilih jenis pembayaran (misal: SPP), tentukan besaran biaya, lalu filter berdasarkan Program Studi dan Angkatan. Sistem akan otomatis membuatkan tagihan berstatus *Belum Lunas* ke seluruh mahasiswa aktif pada kriteria tersebut."
            ],

            // =================================================================================
            // 7. ROLE: PUSTAKAWAN
            // =================================================================================
            [
                'keywords' => 'alur perpustakaan, kelola koleksi, tambah buku, stok buku perpustakaan',
                'jawaban' => "[ROLE: PUSTAKAWAN] Pengelolaan data koleksi buku:\n" .
                             "Pustakawan bertugas memperbarui katalog pustaka melalui menu **Perpustakaan > Koleksi**. Input data buku baru meliputi Judul, Pengarang, Penerbit, ISBN, dan Jumlah Stok. Sistem akan mengatur kolom *jumlah tersedia* secara otomatis sesuai perputaran sirkulasi."
            ],
            [
                'keywords' => 'pinjam buku, pustakawan sirkulasi, kembalikan buku, denda buku, sirkulasi perpustakaan',
                'jawaban' => "[ROLE: PUSTAKAWAN] Layanan sirkulasi peminjaman dan pengembalian:\n" .
                             "1. **Peminjaman:** Catat entri peminjaman di sistem saat mahasiswa meminjam buku. Stok *jumlah tersedia* akan berkurang otomatis.\n" .
                             "2. **Monitoring:** Pantau peminjaman aktif dan daftar keterlambatan melalui Dashboard Pustakawan.\n" .
                             "3. **Pengembalian:** Proses entri pengembalian agar stok buku kembali normal, serta catat nominal denda jika peminjam melewati tanggal jatuh tempo."
            ],

            // =================================================================================
            // 8. ROLE: TENDIK & ADMIN AKADEMIK
            // =================================================================================
            [
                'keywords' => 'tugas tendik, admin absensi, rekap kehadiran, kelola lokasi absen',
                'jawaban' => "[ROLE: TENDIK] Pengelolaan modul presensi/absensi institusi:\n" .
                             "Tenaga Kependidikan bertugas mengatur parameter absensi melalui menu **Absensi > Pengaturan** (termasuk penentuan titik koordinat lokasi/radius presensi) serta menarik laporan rekapitulasi kehadiran mahasiswa dan dosen sebagai bahan evaluasi kedisiplinan."
            ],
            [
                'keywords' => 'dokumen publik, kalender akademik, kelola pengumuman, broadcast notifikasi, berita kampus',
                'jawaban' => "[ROLE: TENDIK / ADMIN] Manajemen informasi dan pusat unduhan:\n" .
                             "1. **Pengumuman:** Membuat edaran informasi/berita kampus yang dilengkapi fitur *Broadcast Notification* agar langsung masuk ke dasbor target user (Semua, Dosen, atau Mahasiswa).\n" .
                             "2. **Dokumen Publik:** Mengunggah file pedoman akademik atau template surat agar mudah diunduh civitas akademika.\n" .
                             "3. **Kalender:** Memperbarui agenda Kalender Akademik secara berkala untuk sinkronisasi jadwal kegiatan universitas."
            ]
        ];

        // Eksekusi penyimpanan ke database
        foreach ($knowledges as $data) {
            ChatbotKnowledge::create($data);
        }
    }
}