<?php

namespace App\Services;

// Import semua model yang akan kita gunakan
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ChatbotService
{
    protected ?string $witAiToken;
    protected string $witApiVersion;
    protected string $defaultResponse = 'Maaf, saya tidak mengerti. Anda bisa bertanya tentang "jadwal kuliah", "info krs", atau "cek tagihan".';

    /**
     * Constructor untuk mengambil konfigurasi dari file .env
     */
    public function __construct()
    {
        $this->witAiToken = config('witai.token');
        $this->witApiVersion = config('witai.api_version');
    }

    /**
     * Fungsi utama untuk memproses pesan dan mendapatkan balasan.
     */
    public function getResponse(string $message): string
    {
        if (!$this->witAiToken) {
            Log::error('WIT_AI_TOKEN tidak diatur di file .env');
            return 'Maaf, layanan asisten virtual sedang tidak terkonfigurasi dengan benar.';
        }

        try {
            $response = Http::withToken($this->witAiToken)
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

            $intent = $data['intents'][0];
            $intentName = $intent['name'];
            $confidence = $intent['confidence'];

            if ($confidence < 0.75) { 
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

    // ===================================================================
    // HANDLER METHODS UNTUK SETIAP INTENT
    // ===================================================================

    // --- INTENT UNTUK ADMIN ---
    protected function handleAdminAktivasiTahunAkademik(array $entities): string
    {
        if (!Auth::user()->hasRole('admin')) {
            return 'Fitur ini hanya untuk Administrator.';
        }
        return 'Untuk mengaktifkan tahun akademik baru, silakan masuk ke menu "Akademik" > "Tahun Akademik", lalu klik tombol "Aktifkan" pada semester yang diinginkan.';
    }

    protected function handleAdminAturPeriodeKRS(array $entities): string
    {
        if (!Auth::user()->hasRole('admin')) {
            return 'Fitur ini hanya untuk Administrator.';
        }
        return 'Pengaturan periode KRS dapat diakses melalui menu "Akademik" > "Tahun Akademik". Anda bisa mengatur tanggal mulai dan selesai pengisian KRS saat membuat atau mengedit tahun akademik.';
    }

    // --- INTENT UNTUK DOSEN & KAPRODI ---
    protected function handleDosenAksesKelasVirtual(array $entities): string
    {
        if (!Auth::user()->hasRole('dosen')) {
            return 'Fitur ini hanya untuk Dosen. Silakan akses menu "Verum" di navigasi atas.';
        }
        return 'Anda dapat mengakses Ruang Kelas Virtual (Verum) melalui menu "Verum" di navigasi atas untuk mengelola materi, tugas, dan diskusi.';
    }

    protected function handleDosenInputNilai(array $entities): string
    {
        if (!Auth::user()->hasRole('dosen')) {
            return 'Fitur ini hanya untuk Dosen.';
        }
        return 'Untuk menginput nilai, silakan masuk ke menu "Dashboard". Di sana akan ada daftar mata kuliah yang Anda ampu, beserta tautan untuk "Input Nilai".';
    }

    protected function handleKaprodiInputNilaiProdi(array $entities): string
    {
        if (!Auth::user()->isKaprodi()) {
            return 'Fitur ini hanya untuk Kepala Program Studi.';
        }
        return 'Sebagai Kaprodi, Anda memiliki wewenang untuk menginput nilai semua mata kuliah di prodi Anda melalui menu "Input Nilai" yang tersedia di portal Kaprodi Anda.';
    }

    protected function handleKaprodiValidasiKRS(array $entities): string
    {
        if (!Auth::user()->isKaprodi()) {
            return 'Fitur ini hanya untuk Kepala Program Studi.';
        }
        return 'Anda dapat memvalidasi (menyetujui/menolak) KRS mahasiswa melalui "Portal Kaprodi" > "Validasi KRS".';
    }

    // --- INTENT UNTUK KEUANGAN ---
    protected function handleKeuanganValidasiPembayaran(array $entities): string
    {
        if (!Auth::user()->hasRole('keuangan')) {
            return 'Fitur ini hanya untuk Staf Keuangan.';
        }
        return 'Untuk memvalidasi pembayaran, silakan akses menu "Manajemen Pembayaran". Anda dapat melihat daftar tagihan dan menandainya sebagai lunas.';
    }

    // --- INTENT UNTUK MAHASISWA ---
    protected function handleMahasiswaCekTagihan(array $entities): string
    {
        if (!Auth::user()->hasRole('mahasiswa')) {
            return 'Fitur ini hanya untuk Mahasiswa.';
        }
        return 'Anda dapat melihat rincian tagihan dan riwayat pembayaran melalui menu "Pembayaran" di dashboard Anda.';
    }

    protected function handleMahasiswaIsiKRS(array $entities): string
    {
        if (!Auth::user()->hasRole('mahasiswa')) {
            return 'Fitur ini hanya untuk Mahasiswa.';
        }
        return 'Untuk mengisi Kartu Rencana Studi (KRS), silakan klik menu "KRS". Pastikan Anda mengisinya pada periode yang aktif dan tidak memiliki tunggakan pembayaran.';
    }

    // --- INTENT UMUM (SUDAH ADA SEBELUMNYA) ---
    protected function handleBiayaKuliah(array $entities): string
    {
        return $this->handleMahasiswaCekTagihan($entities); // Menggunakan handler yang sama
    }

    protected function handleJadwalKuliah(array $entities): string
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('mahasiswa')) {
            return 'Fitur ini hanya tersedia untuk mahasiswa.';
        }
        $mahasiswa = $user->mahasiswa;
        if (!$mahasiswa) {
            return 'Data mahasiswa Anda tidak ditemukan.';
        }

        $specificDay = $this->extractEntityValue($entities, 'wit$local_search_query:local_search_query'); // Wit.ai sering menggunakan ini untuk hari

        $jadwalQuery = Jadwal::whereHas('mataKuliah.mahasiswas', function ($query) use ($mahasiswa) {
            $query->where('mahasiswas.id', $mahasiswa->id);
        });

        if ($specificDay) {
            $jadwalQuery->where('hari', 'like', '%' . $specificDay . '%');
        }

        $jadwals = $jadwalQuery->with('mataKuliah.dosen')->get();

        if ($jadwals->isEmpty()) {
            return 'Jadwal yang Anda cari tidak ditemukan. Pastikan Anda sudah mengisi KRS.';
        }

        $responseText = "Berikut jadwal yang ditemukan:\n";
        foreach ($jadwals as $jadwal) {
            $jamMulai = \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i');
            $jamSelesai = \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i');
            $namaDosen = $jadwal->mataKuliah->dosen->nama_lengkap ?? 'N/A';
            $responseText .= "- {$jadwal->hari}, {$jamMulai}-{$jamSelesai}: {$jadwal->mataKuliah->nama_mk} (Dosen: {$namaDosen})\n";
        }

        return nl2br($responseText);
    }

    // ===================================================================
    // HELPER FUNCTIONS
    // ===================================================================
    private function extractEntityValue(array $entities, string $entityName): ?string
    {
        if (isset($entities[$entityName][0]['value'])) {
            return $entities[$entityName][0]['value'];
        }
        return null;
    }
}
