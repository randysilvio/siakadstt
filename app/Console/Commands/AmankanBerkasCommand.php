<?php

namespace App\Console\Commands;

use App\Support\BerkasPrivat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Memindahkan berkas sensitif lama dari disk public (dapat diakses lewat URL /storage) ke disk privat,
 * lalu memperbarui path di database. Aman dijalankan berulang kali.
 */
class AmankanBerkasCommand extends Command
{
    protected $signature = 'siakad:amankan-berkas {--dry-run : Hanya tampilkan apa yang akan dipindahkan}';

    protected $description = 'Pindahkan bukti bayar, dokumen PMB, draf skripsi, jawaban tugas, SK, dan foto absensi ke penyimpanan privat';

    /** [tabel, kolom] */
    private array $sasaran = [
        ['pembayarans', 'bukti_bayar'],
        ['pmb_documents', 'path_file'],
        ['bimbingan_logs', 'file_draf'],
        ['verum_pengumpulan', 'file_path'],
        ['surat_keputusans', 'file_path'],
        ['absensi_pegawai', 'foto_check_in'],
        ['absensi_pegawai', 'foto_check_out'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $public = Storage::disk('public');
        $privat = Storage::disk(BerkasPrivat::DISK);
        $total = 0;

        foreach ($this->sasaran as [$tabel, $kolom]) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn($tabel, $kolom)) {
                continue;
            }

            $baris = DB::table($tabel)
                ->whereNotNull($kolom)
                ->where($kolom, '!=', '')
                ->where($kolom, 'not like', BerkasPrivat::PREFIX . '/%')
                ->get(['id', $kolom]);

            foreach ($baris as $row) {
                $lama = $row->{$kolom};
                if (!$public->exists($lama)) {
                    continue;
                }

                $baru = BerkasPrivat::PREFIX . '/' . ltrim($lama, '/');
                $this->line(($dryRun ? '[uji] ' : '') . "{$tabel}#{$row->id}: {$lama} -> {$baru}");

                if (!$dryRun) {
                    $privat->put($baru, $public->get($lama));
                    DB::table($tabel)->where('id', $row->id)->update([$kolom => $baru]);
                    $public->delete($lama);
                }
                $total++;
            }
        }

        $this->info(($dryRun ? 'Akan dipindahkan: ' : 'Dipindahkan: ') . $total . ' berkas.');

        return self::SUCCESS;
    }
}
