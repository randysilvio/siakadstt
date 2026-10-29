<?php

namespace App\Models\Concerns;

use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

/**
 * Mencatat buat/ubah/hapus model ke log aktivitas bila dilakukan oleh pengguna yang login.
 * Model dapat mendefinisikan:
 *   - labelAktivitas(): string            nama yang mudah dibaca (mis. "Mahasiswa 2024TH001 · Yohana")
 *   - $kolomTidakDicatat (array)          kolom yang diabaikan
 *   - harusDicatat(string $aksi): bool    syarat tambahan (mis. hanya perubahan status tertentu)
 */
trait TercatatAktivitas
{
    public static function bootTercatatAktivitas(): void
    {
        static::created(fn ($m) => $m->catatAktivitas('buat'));
        static::updated(fn ($m) => $m->catatAktivitas('ubah'));
        static::deleted(fn ($m) => $m->catatAktivitas('hapus'));
    }

    protected function catatAktivitas(string $aksi): void
    {
        if (LogAktivitas::$jeda || (app()->runningInConsole() && !app()->runningUnitTests())) {
            return;
        }
        if (!Auth::guard('web')->id() && !optional(request()->user())->id) {
            return; // proses sistem / tamu tidak dicatat
        }
        if (method_exists($this, 'harusDicatat') && !$this->harusDicatat($aksi)) {
            return;
        }

        $perubahan = null;
        if ($aksi === 'ubah') {
            $abaikan = array_merge(['updated_at', 'created_at', 'remember_token'], property_exists($this, 'kolomTidakDicatat') ? $this->kolomTidakDicatat : []);
            $perubahan = [];
            foreach ($this->getChanges() as $kolom => $baru) {
                if (in_array($kolom, $abaikan, true)) {
                    continue;
                }
                $lama = $this->getOriginal($kolom);
                if (in_array($kolom, ['password'], true)) {
                    $perubahan[$kolom] = ['•••', 'diubah'];
                    continue;
                }
                $perubahan[$kolom] = [self::ringkasNilai($lama), self::ringkasNilai($baru)];
            }
            if (empty($perubahan)) {
                return;
            }
        }

        $label = method_exists($this, 'labelAktivitas') ? $this->labelAktivitas() : class_basename($this) . ' #' . $this->getKey();
        LogAktivitas::catat($aksi, LogAktivitas::LABEL_AKSI[$aksi] . ' ' . $label, $this, $perubahan);
    }

    private static function ringkasNilai($v): ?string
    {
        if ($v === null) {
            return null;
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d H:i');
        }
        if (is_bool($v)) {
            return $v ? 'ya' : 'tidak';
        }
        if (is_array($v)) {
            $v = json_encode($v, JSON_UNESCAPED_UNICODE);
        }
        $v = (string) $v;
        return mb_strlen($v) > 120 ? mb_substr($v, 0, 117) . '…' : $v;
    }
}
