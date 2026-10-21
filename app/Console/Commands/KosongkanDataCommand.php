<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class KosongkanDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:kosongkan-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengosongkan semua data transaksional kecuali user admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses pengosongan data...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            'pembayarans',
            'mahasiswa_mata_kuliah',
            'matakuliah_prasyarat',
            'dosen_mata_kuliah',
            'jadwals',
            'pengumumans',
            'dosens',
            'mahasiswas',
            'tendiks',
            'camabas',
            'pmb_documents',
            'program_studis',
            'mata_kuliahs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("Tabel '{$table}' berhasil dikosongkan.");
            }
        }

        // [PERBAIKAN BUG FATAL FASE 6]: Gunakan relasi many-to-many roles untuk mempertahankan akun Admin
        $adminIds = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->pluck('id')->toArray();

        User::whereNotIn('id', $adminIds)->delete();
        DB::table('role_user')->whereNotIn('user_id', $adminIds)->delete();

        $this->line("Semua user non-admin berhasil dihapus.");

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Proses selesai. Semua data kecuali admin berhasil dikosongkan.');

        return 0;
    }
}