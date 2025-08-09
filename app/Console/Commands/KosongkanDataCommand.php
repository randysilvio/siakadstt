<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

        // Matikan pengecekan foreign key untuk sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Daftar tabel yang akan dikosongkan
        $tables = [
            'pembayarans',
            'mahasiswa_mata_kuliah',
            'matakuliah_prasyarat',
            'jadwals',
            'pengumumans',
            'dosens',
            'mahasiswas',
            'program_studis', // <-- TAMBAHKAN INI
            'mata_kuliahs',   // <-- TAMBAHKAN INI
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $this->line("Tabel '{$table}' berhasil dikosongkan.");
        }

        // Hapus semua user kecuali yang memiliki peran 'admin'
        User::where('role', '!=', 'admin')->delete();
        $this->line("Semua user non-admin berhasil dihapus.");

        // Nyalakan kembali pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Proses selesai. Semua data kecuali admin berhasil dikosongkan.');

        return 0;
    }
}