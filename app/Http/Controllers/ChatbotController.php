<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatbotService;

class ChatbotController extends Controller
{
    /**
     * Properti untuk menyimpan instance dari ChatbotService.
     * @var \App\Services\ChatbotService
     */
    protected ChatbotService $chatbotService;

    /**
     * Constructor akan secara otomatis "menyuntikkan" (inject)
     * ChatbotService, sehingga kita bisa menggunakannya di dalam controller.
     */
    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Method utama untuk menangani pesan yang masuk dari widget chatbot.
     */
    public function handle(Request $request)
    {
        // 1. Validasi input untuk memastikan ada pesan yang dikirim
        $request->validate(['message' => 'required|string|max:255']);

        // 2. Ambil pesan dari request
        $message = $request->input('message');
        
        // 3. Teruskan pesan ke ChatbotService untuk diproses dan dapatkan balasannya
        $response = $this->chatbotService->getResponse($message);

        // 4. Kirim balasan kembali ke frontend dalam format JSON
        return response()->json(['reply' => $response]);
    }
}