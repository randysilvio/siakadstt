<?php

namespace App\Services;

use App\Models\ChatbotKnowledge;
use App\Models\Pengaturan;
use App\Models\PmbPeriod;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    /**
     * Memproses pesan menggunakan Google Gemini API berbasis Dynamic RAG (Retrieval-Augmented Generation)
     * yang diperkaya dengan:
     * 1. Identitas White-Label Kampus (Pengaturan)
     * 2. Status Semester & Gelombang PMB Aktif secara Real-Time
     * 3. Konteks Personal Pengguna yang Sedang Login (Mahasiswa / Dosen / Camaba / Admin)
     * 4. Basis Pengetahuan SOP Database (chatbot_knowledges) + Fallback Lokal Cerdas
     */
    public function getResponse(string $userMessage): string
    {
        $cfg = Pengaturan::allCached();
        $namaKampus = $cfg['nama_kampus'] ?? 'Sekolah Tinggi Teologi (STT) GPI Papua';
        $singkatanKampus = $cfg['singkatan_kampus'] ?? 'STT GPI PAPUA';
        $istilahPembimbing = $cfg['istilah_pembimbing'] ?? 'Dosen Tutor';
        $istilahPerwalian = $cfg['istilah_perwalian'] ?? 'Tutorship';
        $bankKampus = ($cfg['nama_bank_kampus'] ?? 'Bank Papua') . ' No. Rek: ' . ($cfg['no_rekening_kampus'] ?? '-') . ' a.n. ' . ($cfg['atas_nama_rekening'] ?? $singkatanKampus);
        $kontakWa = $cfg['whatsapp_admisi'] ?? '-';
        $emailKampus = $cfg['email_kampus'] ?? 'info@sttgpipapua.ac.id';

        // 1. Ambil Konteks Semester & PMB Aktif dari Database
        $taAktif = TahunAkademik::where('is_active', true)->first();
        $infoSemester = $taAktif
            ? "Tahun Akademik Aktif: {$taAktif->tahun} ({$taAktif->semester}), Status Saklar KRS Global: " . strtoupper($cfg['status_krs'] ?? 'buka')
            : "Belum ada Tahun Akademik yang diaktifkan.";

        $pmbAktif = PmbPeriod::where('is_active', true)->first();
        $infoPmb = $pmbAktif
            ? "Gelombang PMB Aktif: {$pmbAktif->nama_gelombang} (Biaya Pendaftaran: Rp " . number_format($pmbAktif->biaya_pendaftaran, 0, ',', '.') . ", Buka: {$pmbAktif->tanggal_buka} s/d {$pmbAktif->tanggal_tutup})"
            : "Saat ini tidak ada gelombang PMB yang sedang dibuka.";

        $daftarProdi = ProgramStudi::pluck('nama_prodi')->implode(', ');

        // 2. Bangun Konteks Personal Pengguna yang Sedang Login (Role-Aware Context)
        $userContext = $this->buildLoggedInUserContext($taAktif, $istilahPembimbing);

        // 3. Ambil Basis Pengetahuan dari Tabel chatbot_knowledges
        $knowledgeBase = ChatbotKnowledge::all();
        $contextData = "";
        foreach ($knowledgeBase as $data) {
            $contextData .= "- Topik/Kata Kunci [" . $data->keywords . "]:\n  " . $data->jawaban . "\n\n";
        }

        // 4. Pertanyaan tentang data pribadi (IPK, KRS, tagihan, nilai) dijawab langsung oleh server dari database.
        //    Data pribadi mahasiswa TIDAK dikirim ke layanan AI pihak ketiga (prinsip minimisasi data / UU PDP).
        if (Auth::check() && $this->pertanyaanPribadi($userMessage)) {
            return "Shalom! Berikut ringkasan data Anda di SIAKAD saat ini:\n\n" . $userContext;
        }

        // 5. Periksa API Key Gemini; jika kosong, gunakan Mesin Pencari Lokal (Smart Local Fallback)
        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) {
            return $this->getLocalFallbackResponse($userMessage, $knowledgeBase, $singkatanKampus, $bankKampus, $userContext);
        }

        // 5. Susun System Prompt Otak Zoe Generasi Baru (Mencakup Seluruh 22 Modul)
        $bobotHadir = $cfg['bobot_hadir_default'] ?? 10;
        $bobotTugas = $cfg['bobot_tugas_default'] ?? 20;
        $bobotUts   = $cfg['bobot_uts_default'] ?? 30;
        $bobotUas   = $cfg['bobot_uas_default'] ?? 40;

        $systemPrompt = "Kamu adalah Zoe, Asisten Virtual Akademik Pintar resmi untuk layanan Sistem Informasi Akademik (SIAKAD) di {$namaKampus} ({$singkatanKampus}).\n" .
            "Gaya bahasamu cerdas, ramah, sopan, solutif, dan profesional. Selalu gunakan sapaan 'Shalom!' di awal percakapan.\n\n" .
            "=== INFORMASI REAL-TIME INSTITUSI ===\n" .
            "- Nama Institusi: {$namaKampus} ({$singkatanKampus})\n" .
            "- Program Studi Tersedia: {$daftarProdi}\n" .
            "- {$infoSemester}\n" .
            "- {$infoPmb}\n" .
            "- Rekening Resmi Kampus: {$bankKampus}\n" .
            "- Kontak Resmi BAAK/Admisi: WhatsApp {$kontakWa} | Email: {$emailKampus}\n\n" .
            "=== PENGGUNA YANG SEDANG BERTANYA ===\n" .
            "- Peran akun: {$this->peranPengguna()}\n\n" .
            "=== ATURAN BISNIS & SOP 22 MODUL SIAKAD {$singkatanKampus} ===\n" .
            "1. MODUL PMB ONLINE: Calon Mahasiswa Baru (Camaba) mendaftar di menu 'Daftar PMB', login, lalu wajib mengunggah bukti bayar tagihan 'formulir_pmb'. Setelah diverifikasi LUNAS oleh Admin Keuangan, menu 'Biodata PMB' & upload berkas (Ijazah, KK, Pas Foto) terbuka, lanjut cetak 'Kartu Peserta Ujian (PDF)'. Saat dinyatakan LULUS oleh Admin, akun Camaba otomatis berubah menjadi Mahasiswa Aktif lengkap dengan NIM baru.\n" .
            "2. MODUL KRS & KELAS PARALEL (ROMBEL A/B): Mahasiswa mengisi KRS sesuai kuota SKS berdasarkan IPK (IPK >= 3.00 max 24 SKS; 2.50-2.99 max 21 SKS; 2.00-2.49 max 18 SKS; < 2.00 max 15 SKS; Mahasiswa Baru otomatis 24 SKS). Mahasiswa memilih Kelas Paralel (Kelas A/B) selama kursi kuota masih tersedia, lulus MK Prasyarat, dan tidak bentrok jadwal. Syarat buka KRS: Tagihan harus LUNAS atau mendapat status 'DISPENSASI CICILAN' dari Bagian Keuangan, serta sudah mengisi Evaluasi Dosen (EDOM).\n" .
            "3. MODUL {$istilahPerwalian} & VALIDASI KAPRODI: KRS yang diajukan berstatus 'Menunggu Persetujuan' dan harus disetujui (ACC) oleh {$istilahPembimbing} atau Ketua Program Studi (Kaprodi). Mahasiswa akan menerima notifikasi otomatis di ikon lonceng saat KRS disetujui/ditolak.\n" .
            "4. MODUL KHS, TRANSKRIP & GATEKEEPER EDOM/TRACER STUDY: Mahasiswa aktif WAJIB mengisi kuesioner Evaluasi Dosen (EDOM) untuk seluruh mata kuliah yang memiliki dosen pengampu sebelum bisa mencetak PDF KHS dan Transkrip Sementara. Khusus mahasiswa berstatus LULUS (Alumni), wajib mengisi kuesioner 'Tracer Study Alumni' sebelum bisa mengunduh Transkrip Nilai Akhir.\n" .
            "5. MODUL BUKU NILAI OBE & E-LEARNING VERUM: Penilaian menggunakan standar OBE 4 komponen (Kehadiran {$bobotHadir}%, Tugas {$bobotTugas}%, UTS {$bobotUts}%, UAS {$bobotUas}%). Dosen dapat mengklik tombol 'Tarik Data Verum' di Buku Nilai untuk menarik otomatis nilai kehadiran dan rata-rata tugas dari kelas daring Verum, serta mencetak Berita Acara Perkuliahan (BAP) 16 pertemuan.\n" .
            "6. MODUL BIMBINGAN SKRIPSI/TUGAS AKHIR & PPL: Mahasiswa mengajukan judul kajian atau lokasi PPL di menu 'Bimbingan Skripsi & PPL', mengisi Logbook konsultasi per bab, diparaf secara elektronik oleh Dosen Pembimbing 1 & 2, dan mencetak Kartu Kendali Bimbingan (PDF).\n" .
            "7. MODUL LAYANAN SURAT MANDIRI & VERIFIKASI QR-CODE: Mahasiswa dapat mengajukan permohonan surat (Surat Aktif Kuliah, Pengantar Penelitian, Magang, Beasiswa) dari menu 'Permohonan Surat'. Setelah disetujui BAAK, mahasiswa bisa langsung mencetak PDF surat bersegel QR-Code.\n" .
            "8. MODUL TRIDHARMA & BKD DOSEN: Dosen mencatat Penelitian, Pengabdian (PkM), dan Penunjang di menu 'Data Tridharma' untuk memantau pemenuhan SKS BKD (12-16 SKS), mencetak Laporan BKD/CV PDF, dan otomatis tampil di Etalase Riset halaman publik.\n" .
            "9. MODUL PERPUSTAKAAN OPAC & ABSENSI GPS: Pengunjung dapat mencari ketersediaan buku di Katalog Perpustakaan (OPAC). Pegawai & Dosen melakukan presensi kehadiran berbasis koordinat GPS lokasi kampus.\n\n" .
            "=== BASIS PENGETAHUAN TAMBAHAN DARI DATABASE ===\n" .
            $contextData . "\n" .
            "=== INSTRUKSI MENJAWAB ===\n" .
            "- Jawablah secara ramah, ringkas, rapi (gunakan poin/bullet atau penomoran jika menjelaskan langkah-langkah).\n" .
            "- Kamu TIDAK memiliki akses ke data pribadi pengguna. Jika ditanya status dirinya (IPK, KRS, tagihan, nilai), sarankan mengetik 'status saya' atau membuka menu terkait di SIAKAD.\n" .
            "- DILARANG KERAS mengarang nominal biaya atau aturan di luar konteks resmi di atas. Jika tidak tahu, arahkan dengan sopan ke BAAK atau Bagian Keuangan.";

        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        try {
            // API key dikirim lewat header (bukan query string) agar tidak tercatat di log URL/proxy
            $response = Http::withHeaders([
                'Content-Type'   => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])->timeout(18)
              ->retry(2, 1500, function ($exception) {
                  return $exception->getCode() === 503 || $exception->getCode() === 429;
              })
              ->post($url, [
                  'systemInstruction' => [
                      'parts' => [['text' => $systemPrompt]]
                  ],
                  'contents' => [
                      ['role' => 'user', 'parts' => [['text' => $userMessage]]]
                  ],
                  'generationConfig' => [
                      'temperature' => 0.25,
                      'maxOutputTokens' => 800,
                  ]
              ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($result['candidates'][0]['content']['parts'][0]['text']);
                }
            }

            Log::warning('Gemini API Non-200 Response: ' . $response->status());
            return $this->getLocalFallbackResponse($userMessage, $knowledgeBase, $singkatanKampus, $bankKampus, $userContext);

        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return $this->getLocalFallbackResponse($userMessage, $knowledgeBase, $singkatanKampus, $bankKampus, $userContext);
        }
    }

    /**
     * Membangun ringkasan data real-time milik pengguna yang sedang login
     * agar Zoe dapat menjawab pertanyaan personal (IPK, Status KRS, Tagihan, Bimbingan, dll).
     */
    private function buildLoggedInUserContext(?TahunAkademik $taAktif, string $istilahPembimbing): string
    {
        if (!Auth::check()) {
            return "Pengguna saat ini adalah PENGUNJUNG PUBLIK (Belum Login).";
        }

        $user = Auth::user();
        $roles = $user->roles->pluck('display_name')->implode(', ');
        $info = "Nama Pengguna: {$user->name} | Peran Akun: " . ($roles ?: 'Pengguna');

        // Konteks Spesifik Mahasiswa
        if ($user->mahasiswa) {
            $mhs = $user->mahasiswa;
            $prodi = optional($mhs->programStudi)->nama_prodi ?? '-';
            $pa = optional($mhs->dosenWali)->nama_lengkap ?? 'Belum ditentukan';
            $ipk = number_format($mhs->hitungIpk(), 2);
            $sksLulus = $mhs->totalSksLulus();
            $statusEdom = $mhs->status_evaluasi === 'belum_isi' ? 'BELUM SELESAI (Wajib diisi agar KHS/Transkrip tidak terkunci)' : 'SUDAH TUNTAS';

            $tunggakan = \App\Models\Pembayaran::where(function ($q) use ($user, $mhs) {
                    $q->where('user_id', $user->id)->orWhere('mahasiswa_id', $mhs->id);
                })
                ->whereIn('status', ['belum_lunas', 'belum_bayar', 'menunggu_konfirmasi'])
                ->get();

            $infoTagihan = $tunggakan->isEmpty()
                ? 'TIDAK ADA TUNGGAKAN (Semua tagihan Lunas / Dispensasi)'
                : $tunggakan->count() . ' tagihan belum lunas (Total Rp ' . number_format($tunggakan->sum('jumlah'), 0, ',', '.') . ')';

            $info .= "\n- Detail Mahasiswa: NIM {$mhs->nim}, Prodi {$prodi}, Angkatan {$mhs->tahun_masuk}, Status Akademik: {$mhs->status_mahasiswa}"
                  . "\n- {$istilahPembimbing}: {$pa}"
                  . "\n- Prestasi Akademik: IPK Kumulatif {$ipk}, Total SKS Lulus: {$sksLulus} SKS"
                  . "\n- Status KRS Semester Ini: {$mhs->status_krs}" . ($mhs->catatan_kaprodi ? " (Catatan Revisi: {$mhs->catatan_kaprodi})" : "")
                  . "\n- Status Evaluasi Dosen (EDOM): {$statusEdom}"
                  . "\n- Status Keuangan: {$infoTagihan}";
        }

        // Konteks Spesifik Dosen
        if ($user->dosen) {
            $dsn = $user->dosen;
            $jmlWali = \App\Models\Mahasiswa::where('dosen_wali_id', $dsn->id)->where('status_mahasiswa', 'Aktif')->count();
            $antreanKrs = \App\Models\Mahasiswa::where('dosen_wali_id', $dsn->id)->where('status_krs', 'Menunggu Persetujuan')->count();
            $info .= "\n- Detail Dosen: NIDN " . ($dsn->nidn ?? '-') . ", Jabatan: " . ($dsn->jabatan_akademik ?? 'Dosen')
                  . "\n- Mahasiswa Bimbingan: {$jmlWali} mahasiswa aktif ({$antreanKrs} mahasiswa sedang menunggu validasi KRS Anda).";
        }

        // Konteks Spesifik Camaba (PMB)
        if ($user->camaba) {
            $cmb = $user->camaba;
            $info .= "\n- Detail Camaba PMB: No. Pendaftaran {$cmb->no_pendaftaran}, Status Seleksi: " . strtoupper($cmb->status_pendaftaran);
        }

        return $info;
    }

    /**
     * Mesin Pencari Lokal Cerdas (Smart Local RAG Fallback)
     * Bekerja otomatis jika API Key Gemini belum diatur atau sedang gangguan jaringan.
     */
    private function getLocalFallbackResponse(string $userMessage, $knowledgeBase, string $singkatanKampus, string $bankKampus, string $userContext): string
    {
        $pesanLower = strtolower($userMessage);
        $bestMatch = null;
        $highestScore = 0;

        foreach ($knowledgeBase as $item) {
            $keywords = array_map('trim', explode(',', strtolower($item->keywords)));
            $score = 0;
            foreach ($keywords as $kw) {
                if ($kw !== '' && str_contains($pesanLower, $kw)) {
                    $score += strlen($kw);
                }
            }
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $item->jawaban;
            }
        }

        if ($bestMatch && $highestScore > 0) {
            return "Shalom! Berikut informasi resmi terkait pertanyaan Anda:\n\n" . $bestMatch;
        }

        if ($this->pertanyaanPribadi($userMessage)) {
            return "Shalom! Berikut adalah ringkasan status akademik Anda saat ini di sistem:\n\n" . $userContext;
        }

        return "Shalom! Terima kasih telah menghubungi Zoe — Asisten Virtual **{$singkatanKampus}**.\n\n"
             . "Untuk pertanyaan mengenai:\n"
             . "- **KRS & Perwalian:** Buka menu *Akademik > Kartu Rencana Studi (KRS)*.\n"
             . "- **KHS, Transkrip & EDOM:** Pastikan telah mengisi *Evaluasi Dosen (EDOM)* agar KHS & Transkrip dapat dicetak.\n"
             . "- **Pembayaran Kuliah / PMB:** Transfer ke **{$bankKampus}** lalu unggah bukti bayar di menu *Pembayaran*.\n"
             . "- ** Kelas Daring & Tugas:** Buka menu *Verum*.\n\n"
             . "Silakan ketik kata kunci spesifik seperti **\"cara isi krs\"**, **\"edom\"**, **\"skripsi\"**, **\"surat aktif\"**, atau **\"cara bayar spp\"**.";
    }

    /**
     * Mendeteksi pertanyaan tentang data pribadi pengguna (dijawab lokal, tidak dikirim ke API AI).
     */
    private function pertanyaanPribadi(string $pesan): bool
    {
        $p = mb_strtolower($pesan);
        $kataGanti = (bool) preg_match('/\b(saya|aku|punyaku|milikku)\b|\b(ipk|krs|khs|nilai|tagihan|status)ku\b/u', $p);
        $topik = (bool) preg_match('/\b(ipk|ips|krs|khs|nilai|tagihan|tunggakan|bayar|pembayaran|status|nim|sks|edom|dosen wali|tutor)\b/u', $p);
        return ($kataGanti && $topik) || str_contains($p, 'status saya');
    }

    private function peranPengguna(): string
    {
        if (!Auth::check()) {
            return 'Pengunjung publik (belum login)';
        }
        return Auth::user()->roles->pluck('display_name')->implode(', ') ?: 'Pengguna';
    }
}
