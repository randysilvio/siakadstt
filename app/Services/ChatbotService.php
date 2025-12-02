<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected ?string $apiKey;
    
    // PERBAIKAN UTAMA: 
    // Menggunakan 'gemini-2.0-flash' sesuai daftar yang Anda kirimkan.
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    public function getResponse(string $userMessage): string
    {
        if (empty($this->apiKey)) {
            Log::error('GEMINI_API_KEY kosong.');
            return "Error Konfigurasi: API Key tidak ditemukan. Cek config/services.php.";
        }

        try {
            $systemInstruction = $this->buildSystemPrompt();

            // Kirim Request
            $response = Http::withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}?key={$this->apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $systemInstruction . "\n\nUser: " . $userMessage . "\nAI:"]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500,
                    ]
                ]);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $response->body();
                Log::error('Gemini API Error: ' . $errorMessage);
                
                // Jika 2.0 gagal, kita coba fallback ke 'gemini-flash-latest' yang juga ada di daftar Anda
                if (str_contains($errorMessage, 'not found')) {
                    return $this->retryWithFallbackModel($userMessage, $systemInstruction);
                }

                return "Maaf, ada gangguan koneksi ke AI:\n" . $errorMessage;
            }

            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak bisa menjawab (Safety Filter).';
            return nl2br(trim($reply));

        } catch (\Exception $e) {
            return "Terjadi kesalahan sistem:\n" . $e->getMessage();
        }
    }

    // Fungsi cadangan: Menggunakan 'gemini-flash-latest'
    protected function retryWithFallbackModel($userMessage, $systemInstruction)
    {
        try {
            $fallbackUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';
            
            $response = Http::withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$fallbackUrl}?key={$this->apiKey}", [
                    'contents' => [['parts' => [['text' => $systemInstruction . "\n\nUser: " . $userMessage . "\nAI:"]]]]
                ]);
                
            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, fallback model juga gagal.';
            return nl2br(trim($reply));
        } catch (\Exception $e) {
            return "Gagal menghubungkan ke semua model AI.";
        }
    }

    protected function buildSystemPrompt(): string
    {
        $now = Carbon::now();
        $today = $now->isoFormat('dddd, D MMMM Y');
        $jam = $now->format('H:i');

        $userDataContext = "Pengguna: TAMU (Belum Login)";
        
        if (Auth::check() && Auth::user()->hasRole('mahasiswa') && Auth::user()->mahasiswa) {
            $mhs = Auth::user()->mahasiswa;
            $jadwal = $this->getJadwalMahasiswaFormatted($mhs);
            $userDataContext = "User: {$mhs->nama_lengkap} (Mhs)\nJadwal:\n$jadwal";
        } elseif (Auth::check() && Auth::user()->hasRole('dosen')) {
            $userDataContext = "User: " . Auth::user()->name . " (Dosen)";
        }

        return <<<EOT
Kamu adalah ZoeChat, asisten akademik STT GPI Papua.
Waktu: $today $jam.
Konteks: $userDataContext

Jawablah pertanyaan seputar kampus dengan ramah & ringkas.
EOT;
    }

    protected function getJadwalMahasiswaFormatted($mahasiswa): string
    {
        $ta = TahunAkademik::where('is_active', 1)->first();
        if (!$ta) return "Tidak ada semester aktif.";

        $jadwals = Jadwal::whereHas('mataKuliah.mahasiswas', function ($q) use ($mahasiswa, $ta) {
            $q->where('mahasiswas.id', $mahasiswa->id)
              ->where('mahasiswa_mata_kuliah.tahun_akademik_id', $ta->id);
        })->with('mataKuliah.dosen')->get();

        if ($jadwals->isEmpty()) return "Belum ada jadwal.";

        $output = "";
        foreach ($jadwals as $j) {
            $mk = $j->mataKuliah->nama_mk ?? '-';
            $jam = Carbon::parse($j->jam_mulai)->format('H:i');
            $output .= "- {$j->hari}, {$jam}: {$mk}\n";
        }
        return $output;
    }
}