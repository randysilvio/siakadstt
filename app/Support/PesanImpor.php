<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pesan kegagalan impor Excel yang aman ditampilkan: pesan validasi baris ("Baris 5: prodi ... tidak ditemukan")
 * diteruskan apa adanya, sedangkan galat teknis (SQL, berkas rusak) hanya dicatat di log.
 */
class PesanImpor
{
    public static function dari(Throwable $e): string
    {
        if ($e instanceof \RuntimeException && !$e instanceof QueryException && str_starts_with($e->getMessage(), 'Baris ')) {
            return 'Gagal mengimpor data: ' . $e->getMessage();
        }

        Log::error('Gagal impor Excel: ' . $e->getMessage(), ['exception' => $e]);

        return 'Gagal mengimpor data: berkas tidak dapat diproses (format kolom tidak sesuai template atau data bentrok). Detail kesalahan telah dicatat di log sistem.';
    }
}
