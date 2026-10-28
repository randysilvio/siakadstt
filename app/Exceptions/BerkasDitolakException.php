<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Berkas unggahan ditolak pemeriksaan keamanan BerkasPrivat (tipe/isi berbahaya).
 * Turunan RuntimeException agar kode lama yang menangkap RuntimeException tetap berjalan.
 */
class BerkasDitolakException extends RuntimeException
{
}
