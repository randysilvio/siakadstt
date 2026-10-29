<?php

namespace App\Services\Push;

use App\Models\PerangkatPush;
use Illuminate\Support\Facades\Log;

/**
 * Menampung push selama satu request/perintah lalu mengirimnya SETELAH respons dikirim ke pengguna,
 * sehingga simpan data / validasi KRS tidak melambat walau penerimanya ratusan.
 */
class AntrianPush
{
    /** @var array<int, array{user_id:int, judul:string, isi:string, data:array}> */
    private array $antrian = [];
    private bool $terdaftar = false;

    public function __construct(private PushFcm $fcm)
    {
    }

    public function tambah(int $userId, string $judul, string $isi, array $data = []): void
    {
        if (!$this->fcm->aktif()) {
            return;
        }
        $this->antrian[] = ['user_id' => $userId, 'judul' => $judul, 'isi' => $isi, 'data' => $data];
        if (!$this->terdaftar) {
            $this->terdaftar = true;
            app()->terminating(fn () => $this->kirimSemua());
        }
    }

    public function jumlah(): int
    {
        return count($this->antrian);
    }

    public function kirimSemua(): array
    {
        $antrian = $this->antrian;
        $this->antrian = [];
        $total = ['terkirim' => 0, 'gagal' => 0, 'dibuang' => 0];
        if (!$antrian) {
            return $total;
        }

        try {
            $perangkat = PerangkatPush::whereIn('user_id', array_unique(array_column($antrian, 'user_id')))->get()->groupBy('user_id');
            // Pesan yang sama (mis. pengumuman massal) dikirim bersamaan
            $kelompok = [];
            foreach ($antrian as $a) {
                $kunci = md5($a['judul'] . "\0" . $a['isi'] . "\0" . json_encode($a['data']));
                $kelompok[$kunci]['pesan'] = $a;
                $kelompok[$kunci]['perangkat'] = array_merge($kelompok[$kunci]['perangkat'] ?? [], ($perangkat[$a['user_id']] ?? collect())->all());
            }
            foreach ($kelompok as $k) {
                $h = $this->fcm->kirim($k['perangkat'], $k['pesan']['judul'], $k['pesan']['isi'], $k['pesan']['data']);
                foreach ($h as $n => $v) {
                    $total[$n] += $v;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('FCM: pengiriman push gagal: ' . $e->getMessage());
        }
        return $total;
    }
}
