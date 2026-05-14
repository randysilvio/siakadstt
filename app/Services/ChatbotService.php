<?php

namespace App\Services;

use App\Models\ChatbotKnowledge;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    /**
     * Memproses pesan menggunakan Google Gemini API dengan teknik RAG (Retrieval-Augmented Generation)
     * berbasis data pengetahuan lokal di tabel chatbot_knowledges.
     */
    public function getResponse(string $userMessage): string
    {
        // 1. Ambil API Key dari file .env
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return "Sistem asisten virtual belum siap: GEMINI_API_KEY belum dikonfigurasi pada server.";
        }

        // 2. Ambil seluruh data acuan dari database untuk dijadikan konteks AI
        $knowledgeBase = ChatbotKnowledge::all();
        $contextData = "";
        
        foreach ($knowledgeBase as $data) {
            $contextData .= "- Topik/Kata Kunci [" . $data->keywords . "]:\n  " . $data->jawaban . "\n\n";
        }

        // 3. Susun System Prompt (Instruksi Persona & Aturan Batasan AI)
        $systemPrompt = "Kamu adalah Zoe, Asisten Virtual resmi untuk layanan sistem akademik. " .
                        "Gaya bahasamu ramah, sopan, dan profesional. " .
                        "Berikut adalah basis data pengetahuan resmi yang WAJIB kamu gunakan sebagai acuan utama untuk menjawab pertanyaan:\n\n" .
                        $contextData .
                        "Aturan Menjawab:\n" .
                        "1. Jika pertanyaan relevan dengan referensi di atas, susunlah jawaban yang natural, jelas, dan informatif berdasarkan acuan tersebut.\n" .
                        "2. Jika informasi yang ditanyakan sama sekali tidak ada dalam acuan di atas atau di luar konteks akademik, sampaikan permohonan maaf dengan sopan bahwa kamu belum memiliki informasi tersebut, lalu arahkan pengguna untuk menghubungi pihak Admin Akademik / BAAK secara langsung.\n" .
                        "3. Jangan pernah mengarang atau membuat asumsi sendiri (halusinasi) terkait nominal biaya, jadwal, atau aturan jika tidak tertera pada basis pengetahuan.";

        // 4. Endpoint resmi Google Gemini 1.5 Flash
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        try {
            // Panggil API Gemini dengan batasan timeout 15 detik
            $response = Http::timeout(15)->post($url, [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemPrompt]
                    ]
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userMessage]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.3 // Suhu rendah agar AI lebih terstruktur dan patuh pada data acuan
                ]
            ]);

            // 5. Tangkap dan parsing balasan JSON dari Gemini
            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($result['candidates'][0]['content']['parts'][0]['text']);
                }
            }

            // Catat log jika API merespons dengan error (kunci salah, format keliru, dll.)
            Log::error('Gemini API Error: ' . $response->body());
            return "Maaf, Zoe sedang mengalami kendala teknis saat memproses respons. Silakan coba beberapa saat lagi.";

        } catch (\Exception $e) {
            // Catat log jika terjadi timeout atau kendala koneksi server
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return "Terjadi kesalahan koneksi saat menghubungi server AI. Silakan periksa jaringan atau coba kembali.";
        }
    }
}