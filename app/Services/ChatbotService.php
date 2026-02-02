<?php

namespace App\Services;

use App\Models\ChatbotKnowledge;
use Illuminate\Support\Str;

class ChatbotService
{
    /**
     * Logika Manual: Mencocokkan pesan user dengan keywords di database.
     */
    public function getResponse(string $userMessage): string
    {
        // 1. Bersihkan input user (kecilkan huruf)
        $message = strtolower($userMessage);

        // 2. Ambil semua data pengetahuan dari database
        // (Jika data ribuan, sebaiknya di-cache agar cepat)
        $knowledgeBase = ChatbotKnowledge::all();

        // 3. Loop setiap baris data untuk mencari kecocokan
        foreach ($knowledgeBase as $data) {
            // Pecah keywords (karena dipisah koma) -> ["biaya", "spp", "bayar"]
            $keywords = explode(',', strtolower($data->keywords));

            foreach ($keywords as $keyword) {
                // Cek apakah pesan user mengandung salah satu keyword
                // Contoh: User nanya "berapa biaya kuliah?" -> mengandung "biaya"
                if (Str::contains($message, trim($keyword))) {
                    return $data->jawaban;
                }
            }
        }

        // 4. Default Jawaban jika tidak ada yang cocok
        return $this->getFallbackResponse();
    }

    protected function getFallbackResponse(): string
    {
        return "Maaf, saya belum mengerti pertanyaan tersebut. \n\n" .
               "Coba gunakan kata kunci sederhana seperti: \n" .
               "- **Biaya**\n" .
               "- **Jadwal**\n" .
               "- **PMB**\n" .
               "Atau hubungi admin akademik secara langsung.";
    }
}