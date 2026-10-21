<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatbotService;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Properti untuk menyimpan instance dari ChatbotService.
     * @var \App\Services\ChatbotService
     */
    protected ChatbotService $chatbotService;

    /**
     * Inject ChatbotService secara otomatis.
     */
    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Method utama untuk menangani pesan masuk dari widget chatbot.
     */
    public function handle(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        // 2. Ambil pesan dari request
        $message = strip_tags($request->input('message'));
        
        // 3. Teruskan pesan ke ChatbotService untuk diproses oleh Gemini API
        $rawResponse = $this->chatbotService->getResponse($message);

        // 4. [PERBAIKAN KEAMANAN FASE 6]: Konversi Markdown dengan sanitasi HTML & blokir tautan berbahaya (javascript:)
        $formattedResponse = Str::markdown($rawResponse, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        // 5. Kirim balasan kembali ke frontend dalam format JSON
        return response()->json([
            'reply' => $formattedResponse
        ]);
    }
}