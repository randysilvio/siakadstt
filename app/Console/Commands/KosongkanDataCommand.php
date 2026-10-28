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
    protected $signature = 'app:kosongkan-data {--force : Lewati konfirmasi (untuk skrip instalasi)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengosongkan semua data transaksional & master akademik kecuali akun admin dan pengaturan sistem';

    /**
     * Seluruh tabel data (transaksi + master akademik). Yang TIDAK dikosongkan: roles, pengaturans,
     * pengaturan_absensi, lokasi_kerja, chatbot_knowledges, migrations, dan akun admin.
     */
    private const TABEL = [
        // Akademik & KRS
        'mahasiswa_mata_kuliah', 'krs_persetujuans', 'matakuliah_prasyarat', 'dosen_mata_kuliah', 'jadwals',
        'mata_kuliahs', 'kurikulums', 'tahun_akademiks', 'kegiatan_akademik_role', 'kegiatan_akademik',
        // Verum (e-learning)
        'verum_kehadiran', 'verum_presensi', 'verum_pengumpulan', 'verum_tugas', 'verum_materi', 'verum_postingan', 'verum_kelas',
        // Evaluasi dosen
        'evaluasi_jawaban', 'evaluasi_sesi', 'evaluasi_pertanyaan',
        // Bimbingan, surat, dokumen
        'bimbingan_logs', 'bimbingan_tugas_akhirs', 'pengajuan_surats', 'dokumen_terbits',
        'dosen_surat_keputusan', 'surat_keputusans', 'tracer_studies', 'dosen_tridharmas',
        // Keuangan & PMB
        'pembayarans', 'pmb_documents', 'camabas', 'pmb_kepanitiaans', 'pmb_periods',
        // Perpustakaan & absensi
        'peminjamans', 'perpustakaan_eksemplars', 'perpustakaan_koleksi', 'absensi_pegawai',
        // Konten publik & notifikasi
        'pengumumans', 'slideshows', 'dokumen_publiks', 'notifications',
        // Profil & master organisasi
        'dosens', 'mahasiswas', 'tendiks', 'program_studis', 'fakultas',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('PERINGATAN: seluruh data mahasiswa, dosen, KRS, nilai, keuangan, PMB, perpustakaan, dan akun non-admin akan DIHAPUS PERMANEN.');
        $this->line('Database: ' . DB::connection()->getDatabaseName());

        if (!$this->option('force') && !$this->confirm('Yakin ingin mengosongkan data? Tindakan ini tidak dapat dibatalkan.', false)) {
            $this->info('Dibatalkan. Tidak ada data yang diubah.');
            return self::SUCCESS;
        }

        $this->info('Memulai proses pengosongan data...');

        $adminIds = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->pluck('id')->all();

        Schema::disableForeignKeyConstraints();
        try {
            foreach (self::TABEL as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("Tabel '{$table}' berhasil dikosongkan.");
                }
            }

            // Akun non-admin beserta peran, token API, dan sesinya
            DB::table('role_user')->whereNotIn('user_id', $adminIds)->delete();
            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')->where('tokenable_type', User::class)->whereNotIn('tokenable_id', $adminIds)->delete();
            }
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->whereNotIn('user_id', $adminIds)->delete();
            }
            DB::table('users')->whereNotIn('id', $adminIds)->delete();
            $this->line('Semua user non-admin berhasil dihapus.');
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('Proses selesai. Semua data kecuali admin & pengaturan sistem berhasil dikosongkan.');

        return self::SUCCESS;
    }
}
