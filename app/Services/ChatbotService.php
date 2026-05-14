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

        // 4. Endpoint resmi Google Gemini 2.5 Flash
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

        try {
            // Panggil API Gemini secara eksplisit menggunakan header JSON dengan timeout 15 detik
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->timeout(15)->post($url, [
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

            // 5. Tangkap dan parsing balasan JSON dari Gemini jika sukses
            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($result['candidates'][0]['content']['parts'][0]['text']);
                }
            }

            // === TAMPILKAN ERROR ASLI DARI GOOGLE KE LAYAR WIDGET ===
            // Jika status HTTP bukan 200 OK, kita ambil isi body responsenya dan lemparkan ke user
            // agar kita tahu pasti apa yang ditolak oleh Google (misal: API_KEY_INVALID, dll)
            $errorBody = $response->body();
            Log::error('Gemini API Error: ' . $errorBody);
            
            return "⚠️ **Error dari Server Google:** \n```json\n" . $errorBody . "\n```\n*Silakan berikan informasi error di atas kepada pengembang.*";

        } catch (\Exception $e) {
            // Catat log jika terjadi timeout atau kendala koneksi HTTP
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return "⚠️ **Kesalahan Sistem/Koneksi:** \n" . $e->getMessage();
        }
    }
}