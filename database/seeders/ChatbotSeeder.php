<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotKnowledge;
use Illuminate\Support\Facades\DB;

class ChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Bersihkan tabel sebelum mengisi ulang agar tidak duplikat
        DB::table('chatbot_knowledges')->truncate();

        $data = [
            // ============================================================
            // 1. SAPAAN & IDENTITAS (Dialek Papua & Indonesia Baku)
            // ============================================================
            [
                'keywords' => 'halo,hai,shalom,syalom,selamat pagi,selamat siang,selamat sore,selamat malam,tes,pace,mace,kaka,sodara,woi,permisi',
                'jawaban' => 'Shalom! Selamat datang di SIAKAD STT GPI Papua. Saya Zoe, asisten virtual Anda. Ada yang bisa sa bantu? Boleh tanya soal akademik, keuangan, atau cara pakai aplikasi.'
            ],
            [
                'keywords' => 'siakad,aplikasi apa,sistem ini,ini apa,barang apa ini',
                'jawaban' => 'Ini adalah Sistem Informasi Akademik (SIAKAD) terintegrasi untuk Mahasiswa, Dosen, dan Staff. Tersedia versi Website untuk administrasi dan Aplikasi Mobile (Android) untuk absensi.'
            ],
            [
                'keywords' => 'alamat,lokasi,kampus dimana,fakfak,kampus pu tempat,di mana',
                'jawaban' => 'Kampus STT GPI Papua berlokasi di Jl. Jenderal Sudirman, Fakfak, Papua Barat. Mari main ke kampus kalau ada perlu!'
            ],

            // ============================================================
            // 2. KEUANGAN & PEMBAYARAN (Sangat Detail)
            // ============================================================
            [
                'keywords' => 'rekening,transfer kemana,nomor rekening,bank,kirim uang kemana,mo bayar lewat apa,norek',
                'jawaban' => 'Pembayaran bisa ditransfer ke Bank Papua: --- a.n STT GPI Papua. Ingat simpan struk/bukti transfer e!'
            ],
            [
                'keywords' => 'cara bayar,bayar spp,upload bukti,konfirmasi pembayaran,bikin tagihan,sa mo bayar,bagaimana cara bayar',
                'jawaban' => 'Caranya gampang: 1. Login SIAKAD. 2. Masuk menu "Keuangan". 3. Pilih tagihan (SPP/SKS). 4. Klik "Bayar" lalu upload foto bukti transfer (JANGAN lupa foto dulu bukti transfernya, format JPG/PNG max 2MB).'
            ],
            [
                'keywords' => 'riwayat pembayaran,history bayar,lihat pembayaran dimana,cek uang su masuk ka belum,histori,sa su bayar apa saja',
                'jawaban' => 'Untuk lihat riwayat: Buka menu "Riwayat Pembayaran" di dashboard mahasiswa. Di situ ada daftar semua yang sudah ko bayar lengkap dengan tanggal dan status lunasnya.'
            ],
            [
                'keywords' => 'status pembayaran,cek pembayaran,kapan lunas,menunggu konfirmasi,su bayar tapi belum lunas,kenapa belum lunas',
                'jawaban' => 'Kalau status "Menunggu Konfirmasi", sabar e, bagian keuangan lagi cek ko pu bukti transfer. Kalau su oke, nanti status berubah jadi "Lunas" otomatis.'
            ],
            [
                'keywords' => 'jenis tagihan,biaya apa saja,spp,sks,uang gedung,wisuda,bayar apa saja,biaya kuliah',
                'jawaban' => 'Di SIAKAD ada tagihan: SPP, Uang Gedung, Biaya SKS, Registrasi Ulang, Biaya PPL, dan Wisuda. Cek menu "Keuangan" untuk lihat apa yang harus dibayar semester ini.'
            ],

            // ============================================================
            // 3. AKADEMIK MAHASISWA (KRS, KHS, Nilai)
            // ============================================================
            [
                'keywords' => 'kapan isi krs,jadwal krs,mulai krs kapan,su bisa krs kah,kapan mulai isi krs',
                'jawaban' => 'Pengisian KRS biasanya dibuka di awal semester baru. Cek Kalender Akademik atau tunggu Pengumuman di dashboard. Pastikan su lunas tagihan registrasi dulu baru bisa isi nah.'
            ],
            [
                'keywords' => 'krs,isi krs,rencana studi,kartu rencana studi,cara isi krs,mo isi krs',
                'jawaban' => 'Isi KRS lewat menu "KRS Online". Pilih mata kuliah yang dibuka semester ini. Kalau bingung, tanya Dosen Wali.'
            ],
            [
                'keywords' => 'validasi krs,persetujuan krs,belum disetujui,dosen wali belum acc,su kirim krs tapi belum,acc krs,kenapa krs belum divalidasi',
                'jawaban' => 'KRS ko itu harus disetujui (ACC) sama Dosen Wali dulu. Coba kontak Dosen Wali. Kalau beliau berhalangan, Kaprodi juga bisa bantu validasi dari sistem.'
            ],
            [
                'keywords' => 'nilai,khs,transkrip,ipk,hasil studi,lihat nilai dimana,cek transkrip,sa pu nilai',
                'jawaban' => 'Mau lihat nilai? Buka menu "KHS & Nilai". Di situ bisa lihat IPK dan transkrip lengkap. Bisa langsung cetak PDF juga.'
            ],
            [
                'keywords' => 'absensi mahasiswa,cara absen,hadir kuliah,absen lewat hp,tra bisa absen',
                'jawaban' => 'Kalau kuliah tatap muka, dosen yang absen. Kalau kuliah online di menu Verum, ko tinggal klik tombol "Hadir" pas kelas dibuka.'
            ],

            // ============================================================
            // 4. DOSEN & MENGAJAR (Spesifik Pengajar)
            // ============================================================
            [
                'keywords' => 'jadwal mengajar,jadwal dosen,sa pu jadwal,kapan sa mengajar,cek jadwal',
                'jawaban' => 'Bapak/Ibu Dosen bisa lihat jadwal mengajar di halaman depan Dashboard setelah login. Bisa juga download PDF lewat tombol "Cetak Jadwal".'
            ],
            [
                'keywords' => 'rps,upload rps,rencana pembelajaran,kasih naik rps,input rps,file rps',
                'jawaban' => 'Upload RPS itu wajib. Caranya: Klik mata kuliah di dashboard, terus cari tombol Upload RPS. File harus PDF dan tidak boleh lebih dari 5MB e.'
            ],
            [
                'keywords' => 'perwalian,dosen wali,bimbingan,mahasiswa wali,validasi anak wali,acc krs mahasiswa',
                'jawaban' => 'Buka menu "Perwalian" untuk lihat mahasiswa bimbingan. Bapak/Ibu bisa validasi (Approve) atau tolak KRS dong dari situ.'
            ],
            [
                'keywords' => 'absensi dosen,clock in,clock out,absen kerja,absen datang,absen pulang,cara absen dosen',
                'jawaban' => 'Absensi kerja pakai Aplikasi Mobile (Android). Tekan "CLOCK IN" pas datang, dan "CLOCK OUT" pas pulang. Harus nyalakan GPS dan izinkan Kamera e.'
            ],

            // ============================================================
            // 5. PENERIMAAN MAHASISWA BARU (PMB)
            // ============================================================
            [
                'keywords' => 'daftar baru,camaba,mahasiswa baru,pmb,cara daftar,mo daftar kuliah,jadi mahasiswa baru',
                'jawaban' => 'Mau daftar kuliah? Gampang. Klik "Daftar PMB" di halaman depan web ini. Bikin akun, login, bayar formulir, terus isi biodata lengkap.'
            ],
            [
                'keywords' => 'biaya pmb,harga formulir,formulir berapa,bayar pendaftaran',
                'jawaban' => 'Biaya formulir PMB nanti muncul di dashboard Camaba setelah login. Transfer sesuai nominal itu supaya akun bisa diverifikasi admin.'
            ],

            // ============================================================
            // 6. TEKNIS & KEAMANAN (Login, Password, Error)
            // ============================================================
            [
                'keywords' => 'lupa password,reset password,tidak bisa login,tra bisa login,lupa sandi,akun terkunci,tra bisa masuk',
                'jawaban' => 'Aduh lupa sandi? Klik "Lupa Password" di layar login, nanti link reset dikirim ke email. Atau kontak Admin Akademik biar dong reset manual.'
            ],
            [
                'keywords' => 'logout,keluar otomatis,sesi habis,tiba-tiba keluar,kenapa logout sendiri',
                'jawaban' => 'Itu fitur keamanan. Kalau ko tra bikin apa-apa selama 10 menit, sistem otomatis logout biar akun aman. Jadi rajin-rajin simpan data e.'
            ],
            [
                'keywords' => 'ktm,kartu mahasiswa,kartu digital,liat ktm dimana,download ktm',
                'jawaban' => 'KTM Digital ada di Aplikasi Mobile. Login di HP, nanti muncul kartu ada QR Code-nya. Bisa disimpan/download juga.'
            ],
            [
                'keywords' => 'verum,e-learning,kuliah online,tugas online,daring',
                'jawaban' => 'Verum itu tempat kuliah online di SIAKAD. Dosen bisa kasih materi & tugas, mahasiswa bisa kerja tugas & diskusi di situ.'
            ],
        ];

        // Masukkan data ke database
        foreach ($data as $item) {
            ChatbotKnowledge::create($item);
        }
    }
}