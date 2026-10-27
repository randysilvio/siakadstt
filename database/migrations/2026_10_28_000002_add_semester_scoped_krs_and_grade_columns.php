<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1. krs_persetujuans — status persetujuan KRS per (mahasiswa, tahun akademik). Kolom mahasiswas.status_krs
 *    tetap dipakai sebagai salinan status semester AKTIF agar tampilan lama tidak berubah, tetapi keputusan
 *    untuk semester lain (EDOM, Verum, rekap nilai semester lalu) kini dibaca dari tabel ini.
 * 2. mahasiswa_mata_kuliah.nilai_manual — huruf mutu yang dipilih dosen secara manual tidak ditimpa konversi.
 * 3. verum_kelas.rombel — kelas Verum terikat ke rombel KRS (kosong = berlaku untuk semua rombel).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('krs_persetujuans')) {
            Schema::create('krs_persetujuans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
                $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks')->cascadeOnDelete();
                $table->string('status', 40)->default('Belum Mengajukan');
                $table->text('catatan')->nullable();
                $table->foreignId('divalidasi_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('divalidasi_pada')->nullable();
                $table->timestamps();
                $table->unique(['mahasiswa_id', 'tahun_akademik_id'], 'uniq_krs_persetujuan_mhs_ta');
                $table->index(['tahun_akademik_id', 'status'], 'idx_krs_persetujuan_ta_status');
            });

            $aktifId = DB::table('tahun_akademiks')->where('is_active', true)->whereNull('deleted_at')->value('id');
            $now = now();

            $pasangan = DB::table('mahasiswa_mata_kuliah')
                ->select('mahasiswa_id', 'tahun_akademik_id')
                ->whereNotNull('tahun_akademik_id')
                ->groupBy('mahasiswa_id', 'tahun_akademik_id')
                ->get();

            $statusAktif = DB::table('mahasiswas')->pluck('status_krs', 'id');
            $catatanAktif = DB::table('mahasiswas')->pluck('catatan_kaprodi', 'id');

            foreach ($pasangan->chunk(500) as $chunk) {
                $rows = [];
                foreach ($chunk as $p) {
                    $isAktif = (int) $p->tahun_akademik_id === (int) $aktifId;
                    $rows[] = [
                        'mahasiswa_id'      => $p->mahasiswa_id,
                        'tahun_akademik_id' => $p->tahun_akademik_id,
                        // Semester aktif mengikuti status saat ini; KRS semester lampau yang tercatat di sistem
                        // dianggap telah disetujui (data lama tidak menyimpan riwayat persetujuan per semester).
                        'status'            => $isAktif ? ($statusAktif[$p->mahasiswa_id] ?: 'Belum Mengajukan') : 'Disetujui',
                        'catatan'           => $isAktif ? ($catatanAktif[$p->mahasiswa_id] ?? null) : null,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
                DB::table('krs_persetujuans')->insert($rows);
            }
        }

        if (!Schema::hasColumn('mahasiswa_mata_kuliah', 'nilai_manual')) {
            Schema::table('mahasiswa_mata_kuliah', function (Blueprint $table) {
                $table->boolean('nilai_manual')->default(false)->after('nilai');
            });

            // Nilai huruf lama yang diisi tanpa komponen angka sama sekali adalah nilai manual dosen.
            DB::table('mahasiswa_mata_kuliah')
                ->whereNotNull('nilai')
                ->whereNull('nilai_kehadiran')->whereNull('nilai_tugas')
                ->whereNull('nilai_uts')->whereNull('nilai_uas')
                ->update(['nilai_manual' => true]);
        }

        if (Schema::hasTable('verum_kelas') && !Schema::hasColumn('verum_kelas', 'rombel')) {
            Schema::table('verum_kelas', function (Blueprint $table) {
                $table->string('rombel', 30)->nullable()->after('nama_kelas');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('verum_kelas', 'rombel')) {
            Schema::table('verum_kelas', fn (Blueprint $t) => $t->dropColumn('rombel'));
        }
        if (Schema::hasColumn('mahasiswa_mata_kuliah', 'nilai_manual')) {
            Schema::table('mahasiswa_mata_kuliah', fn (Blueprint $t) => $t->dropColumn('nilai_manual'));
        }
        Schema::dropIfExists('krs_persetujuans');
    }
};
