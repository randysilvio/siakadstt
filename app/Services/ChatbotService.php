<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotService
{
    protected ?string $witAiToken;
    protected string $witApiVersion;
    protected string $defaultResponse = 'Maaf, saya kurang mengerti. Anda bisa bertanya seperti, "cara isi krs", "jadwal kuliah hari selasa", atau "info tentang stt gpi papua".';

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
            $response = Http::withToken((string) $this->witAiToken)
                ->get('https://api.wit.ai/message', [
                    'v' => $this->witApiVersion,
                    'q' => $message,
                ]);

            if (!$response->successful()) {
                throw new \Exception('Gagal menghubungi Wit.ai API. Status: ' . $response->status());
            }

            $data = $response->json();
            
            if (empty($data['intents'])) {
                return $this->defaultResponse;
            }

            $intent = $data['intents'][0] ?? [];
            $intentName = $intent['name'] ?? null;
            $confidence = $intent['confidence'] ?? 0;

            if ($confidence < 0.75 || !$intentName) { 
                return $this->defaultResponse;
            }

            $entities = $data['entities'] ?? [];
            $handlerMethod = 'handle' . Str::studly($intentName);

            if (method_exists($this, $handlerMethod)) {
                return $this->{$handlerMethod}($entities);
            }

            return $this->defaultResponse;

        } catch (\Exception $e) {
            Log::error('Error saat berkomunikasi dengan Wit.ai: ' . $e->getMessage());
            return 'Maaf, terjadi kesalahan saat menghubungi asisten virtual.';
        }
    }

    protected function handleSapaan(array $entities): string
    {
        $responses = [
            "Shalom! Selamat datang di ZoeChat, asisten virtual SIAKAD STT GPI Papua. Ada yang bisa saya bantu?",
            "Halo! Saya ZoeChat, siap membantu Anda menavigasi SIAKAD STT GPI Papua. Apa yang ingin Anda ketahui?"
        ];
        return $responses[array_rand($responses)];
    }
    
    protected function handleTerimaKasih(array $entities): string
    {
        return 'Sama-sama! Senang bisa membantu Anda.';
    }

    protected function handleInfoKampus(array $entities): string
    {
        $responseText = "Sekolah Tinggi Theologia (STT) Gereja Protestan Indonesia (GPI) di Papua adalah lembaga pendidikan tinggi teologi yang berlokasi di Fakfak, Papua Barat. Kami berkomitmen untuk mempersiapkan para pemimpin gereja dan pelayan Tuhan yang kompeten dan berintegritas untuk melayani di tanah Papua dan sekitarnya.\n\n";
        $responseText .= "SIAKAD ini adalah sistem digital terpusat kami untuk membantu seluruh civitas akademika—mahasiswa, dosen, dan staf—dalam mengelola kegiatan perkuliahan secara efisien dan transparan.";
        return nl2br($responseText);
    }

    protected function handleMahasiswaIsiKRS(array $entities): string
    {
        $responseText = "Tentu, berikut adalah langkah-langkah detail untuk mengisi Kartu Rencana Studi (KRS):\n\n";
        $responseText .= "1. **Login** ke akun SIAKAD Anda.\n";
        $responseText .= "2. Dari menu navigasi, klik **KRS**.\n";
        $responseText .= "3. Halaman akan menampilkan daftar mata kuliah yang tersedia untuk semester aktif.\n";
        $responseText .= "4. **Centang** kotak di sebelah kiri nama mata kuliah yang ingin Anda ambil.\n";
        $responseText .= "5. Perhatikan **Total SKS Diambil** di bagian atas untuk memastikan tidak melebihi batas maksimum Anda.\n";
        $responseText .= "6. Jika sudah yakin, klik tombol **\"Simpan KRS\"**. Status KRS Anda akan berubah menjadi \"Menunggu Persetujuan\" dari Kaprodi.";
        return nl2br($responseText);
    }
    
    protected function handleMahasiswaAksesVerum(array $entities): string
    {
        $responseText = "Tentu, berikut adalah panduan untuk mengakses Kelas Virtual (Verum) sebagai mahasiswa:\n\n";
        $responseText .= "1. **Login** ke akun SIAKAD Anda.\n";
        $responseText .= "2. Dari menu navigasi utama di bagian atas, klik **Verum**.\n";
        $responseText .= "3. Anda akan melihat daftar semua kelas virtual dari mata kuliah yang Anda ambil di semester ini.\n";
        $responseText .= "4. Klik pada salah satu kelas untuk masuk dan melihat **materi**, **tugas**, dan **forum diskusi**.";
        return nl2br($responseText);
    }

    protected function handleJadwalKuliah(array $entities): string
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('mahasiswa') || !$user->mahasiswa) {
            return 'Fitur ini hanya tersedia untuk mahasiswa yang sedang login.';
        }
        $mahasiswa = $user->mahasiswa;

        $specificDay = $this->extractEntityValue($entities, 'hari:hari');
        $tahunAkademikAktif = TahunAkademik::where('is_active', 1)->first();
        if (!$tahunAkademikAktif) {
            return 'Saat ini tidak ada semester yang aktif.';
        }

        $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
        
        $jadwalQuery = Jadwal::whereHas('mataKuliah.mahasiswas', function ($query) use ($mahasiswa, $tahunAkademikAktif) {
            $query->where('mahasiswas.id', $mahasiswa->id)
                  ->where('mahasiswa_mata_kuliah.tahun_akademik_id', $tahunAkademikAktif->id);
        });

        if ($specificDay) {
            $jadwalQuery->where('hari', 'like', '%' . $specificDay . '%');
        }

        $jadwals = $jadwalQuery->with('mataKuliah.dosen.user')
            ->get()
            ->sortBy(fn($jadwal) => $hariOrder[$jadwal->hari] ?? 99);

        if ($jadwals->isEmpty()) {
            return $specificDay ? "Tidak ada jadwal kuliah untuk hari {$specificDay}." : 'Anda belum memiliki jadwal kuliah untuk semester ini.';
        }

        $responseText = $specificDay ? "Berikut adalah jadwal kuliah Anda untuk hari **{$specificDay}**:\n" : "Berikut adalah jadwal kuliah Anda untuk semester ini:\n";
        foreach ($jadwals as $jadwal) {
            $jamMulai = Carbon::parse($jadwal->jam_mulai)->format('H:i');
            $jamSelesai = Carbon::parse($jadwal->jam_selesai)->format('H:i');
            $namaDosen = optional(optional($jadwal->mataKuliah)->dosen)->nama_lengkap ?? 'N/A';
            $responseText .= "- **{$jamMulai} - {$jamSelesai}**: {$jadwal->mataKuliah->nama_mk} (Dosen: {$namaDosen})\n";
        }

        return nl2br($responseText);
    }

    private function extractEntityValue(array $entities, string $entityName): ?string
    {
        return $entities[$entityName][0]['value'] ?? null;
    }
}