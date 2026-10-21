<?php

namespace App\Services;

use App\Models\ChatbotKnowledge;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    /**
     * Memproses pesan menggunakan Google Gemini API berbasis RAG (Retrieval-Augmented Generation)
     * yang diperkaya dengan Master Context aturan bisnis SIAKAD STT GPI Papua.
     */
    public function getResponse(string $userMessage): string
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            return "Sistem asisten virtual belum siap: Konfigurasi layanan AI belum diaktifkan pada server.";
        }

        $knowledgeBase = ChatbotKnowledge::all();
        $contextData = "";
        
        foreach ($knowledgeBase as $data) {
            $contextData .= "- Topik/Kata Kunci [" . $data->keywords . "]:\n  " . $data->jawaban . "\n\n";
        }

        $systemPrompt = "Kamu adalah Zoe, Asisten Virtual resmi untuk layanan Sistem Informasi Akademik (SIAKAD) di Sekolah Tinggi Teologi (STT) GPI Papua. " .
                        "Gaya bahasamu cerdas, ramah, sopan, dan profesional. Selalu gunakan sapaan 'Shalom' saat memulai percakapan baru.\n\n" .
                        "SIAKAD STT GPI Papua memiliki beberapa modul dan aturan bisnis baku berikut:\n" .
                        "1. MODUL PMB (Penerimaan Mahasiswa Baru): Calon Mahasiswa Baru (Camaba) wajib melakukan pembayaran tagihan 'formulir_pmb' terlebih dahulu. Setelah admin keuangan memverifikasi status tagihan menjadi LUNAS, barulah form pengisian biodata dan upload berkas (Ijazah, KK, Pas Foto) terbuka di dashboard Camaba.\n" .
                        "2. MODUL KRS & PERWALIAN: Mahasiswa wajib mengisi KRS di awal semester sesuai batas maksimal SKS yang ditentukan oleh IPK semester sebelumnya (IPK >= 3.00 max 24 SKS, >= 2.50 max 21 SKS, dst.). Pengambilan mata kuliah harus memperhatikan syarat kelulusan mata kuliah prasyarat dan tidak boleh bentrok jadwal. KRS yang diajukan harus disetujui (divalidasi) oleh Dosen Wali dan Kaprodi.\n" .
                        "3. MODUL KHS & TRANSKRIP (Aturan EDOM): Sistem menerapkan proteksi ketat. Mahasiswa WAJIB menyelesaikan pengisian kuesioner Evaluasi Dosen (EDOM) untuk seluruh mata kuliah di semester aktif sebelum diizinkan melihat atau mencetak Kartu Hasil Studi (KHS) dan Transkrip Nilai.\n" .
                        "4. MODUL KEUANGAN: Pengelolaan tagihan, potongan, dan verifikasi status pembayaran (Lunas/Belum Lunas) merupakan wewenang mutlak bagian Admin Keuangan. Mahasiswa hanya dapat melihat tagihan dan mengunggah bukti transfer.\n" .
                        "5. MODUL E-LEARNING (VERUM): Kelas digital diakses berdasarkan data KRS. Mahasiswa hanya bisa masuk ke ruang pertemuan online (meeting) jika mata kuliah tersebut resmi terdaftar di KRS dan sesi pertemuannya telah dibuka oleh Dosen Pengampu.\n\n" .
                        "Berikut adalah rincian data operasional/SOP spesifik dari database yang WAJIB dijadikan pedoman menjawab:\n" .
                        $contextData .
                        "ATURAN MUTLAK:\n" .
                        "- Jika pertanyaan relevan dengan konteks atau acuan SOP di atas, berikan jawaban yang natural, informatif, dan terstruktur.\n" .
                        "- Jika informasi yang ditanyakan sama sekali tidak ada pada acuan atau di luar konteks akademik, sampaikan permohonan maaf dengan sopan bahwa kamu belum memiliki informasi tersebut, lalu sarankan pengguna untuk menghubungi Admin Akademik (BAAK) atau Bagian Keuangan secara langsung.\n" .
                        "- DILARANG KERAS mengarang, memanipulasi, atau mengasumsikan sendiri nominal biaya kuliah, jadwal spesifik, atau aturan lain jika tidak tertera secara tertulis pada referensi di atas.";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->timeout(15)
              ->retry(2, 2000, function ($exception, $request) {
                  return $exception->getCode() === 503;
              })
              ->post($url, [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]]
                ],
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $userMessage]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.2
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($result['candidates'][0]['content']['parts'][0]['text']);
                }
            }

            if ($response->status() === 503) {
                return "Mohon maaf, jalur server asisten virtual saat ini sedang dipadati permintaan. Silakan tunggu beberapa saat dan coba kirimkan kembali pertanyaan Anda.";
            }

            // [PERBAIKAN KEAMANAN FASE 6]: Catat detail error di log internal tanpa mengeksposnya ke pengguna
            Log::error('Gemini API Error: ' . $response->body());
            
            return "Mohon maaf, saat ini layanan asisten virtual sedang mengalami gangguan teknis sementara. Silakan coba beberapa saat lagi atau hubungi bagian akademik (BAAK).";

        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return "Tidak dapat terhubung ke server asisten virtual saat ini. Silakan periksa koneksi Anda atau coba kembali nanti.";
        }
    }
}