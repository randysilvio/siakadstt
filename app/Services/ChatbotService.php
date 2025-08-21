<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotService
{
    protected ?string $witAiToken;
    protected string $witApiVersion;
    protected string $defaultResponse = 'Maaf, saya tidak mengerti pertanyaan Anda. Coba gunakan kata kunci lain.';

    public function __construct()
    {
        $this->witAiToken = config('witai.token');
        $this->witApiVersion = config('witai.api_version');
    }

    public function getResponse(string $message): string
    {
        if (!$this->witAiToken) {
            Log::error('WIT_AI_TOKEN tidak diatur di file .env');
            return 'Maaf, layanan asisten virtual sedang tidak terkonfigurasi dengan benar.';
        }

        try {
            // Kirim permintaan ke Wit.ai Message API
            $response = Http::withToken($this->witAiToken)
                ->get('https://api.wit.ai/message', [
                    'v' => $this->witApiVersion,
                    'q' => $message,
                ]);

            if (!$response->successful()) {
                throw new \Exception('Gagal menghubungi Wit.ai API. Status: ' . $response->status());
            }

            $data = $response->json();
            
            // Cek apakah Wit.ai mendeteksi intent
            if (empty($data['intents'])) {
                return $this->defaultResponse;
            }

            // Ambil intent dengan tingkat kepercayaan (confidence) tertinggi
            $intent = $data['intents'][0];
            $intentName = $intent['name'];
            $confidence = $intent['confidence'];

            // Abaikan jika tingkat kepercayaan terlalu rendah
            if ($confidence < 0.7) { // Anda bisa menyesuaikan ambang batas ini
                return $this->defaultResponse;
            }

            // Panggil method lokal berdasarkan nama intent yang terdeteksi
            // Contoh: Intent "Jadwal_Kuliah" akan memanggil method "handleJadwalKuliah"
            $handlerMethod = 'handle' . Str::studly($intentName); 
            if (method_exists($this, $handlerMethod)) {
                return $this->{$handlerMethod}();
            }

            return $this->defaultResponse;

        } catch (\Exception $e) {
            Log::error('Error saat berkomunikasi dengan Wit.ai: ' . $e->getMessage());
            return 'Maaf, terjadi kesalahan saat menghubungi asisten virtual.';
        }
    }

    /**
     * Menangani intent 'Biaya_Kuliah'.
     * NAMA METHOD DISESUAIKAN
     */
    protected function handleBiayaKuliah(): string
    {
        return 'Informasi mengenai biaya kuliah dapat dilihat pada menu Pembayaran di dashboard Anda.';
    }

    /**
     * Menangani intent 'Jadwal_Kuliah'.
     * NAMA METHOD DISESUAIKAN
     */
    protected function handleJadwalKuliah(): string
    {
        $user = Auth::user();

        // Pastikan yang bertanya adalah mahasiswa
        if (!$user || $user->role !== 'mahasiswa') {
            return 'Fitur ini hanya tersedia untuk mahasiswa. Silakan login sebagai mahasiswa untuk melihat jadwal kuliah.';
        }

        $mahasiswa = $user->mahasiswa;
        if (!$mahasiswa) {
            return 'Data mahasiswa Anda tidak ditemukan. Mohon hubungi administrasi.';
        }

        // Query untuk mengambil jadwal
        $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
        $jadwals = Jadwal::whereHas('mataKuliah.mahasiswas', function ($query) use ($mahasiswa) {
                $query->where('mahasiswas.id', $mahasiswa->id);
            })
            ->with('mataKuliah')
            ->get()
            ->sortBy(function($jadwal) use ($hariOrder) {
                return $hariOrder[$jadwal->hari] ?? 99;
            });

        if ($jadwals->isEmpty()) {
            return 'Anda belum memiliki jadwal kuliah untuk semester ini. Silakan isi KRS terlebih dahulu.';
        }

        // Format jawaban menjadi string yang mudah dibaca
        $responseText = "Berikut adalah jadwal kuliah Anda:\n";
        foreach ($jadwals as $jadwal) {
            $jamMulai = \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i');
            $jamSelesai = \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i');
            $responseText .= "- {$jadwal->hari}, {$jamMulai}-{$jamSelesai}: {$jadwal->mataKuliah->nama_mk}\n";
        }

        return $responseText;
    }
}