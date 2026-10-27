<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kepanitiaan PMB per tahun penerimaan.
 *
 * - pmb_periods.tahun_pmb: tahun angkatan yang diterima gelombang ini (gelombang I bisa dibuka akhir tahun sebelumnya).
 * - pmb_kepanitiaans: anggota panitia per tahun PMB. Akses panitia berlaku selama tahun tersebut belum ditutup admin.
 * - Jejak audit: siapa memverifikasi pembayaran dan siapa memutuskan hasil seleksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pmb_periods', 'tahun_pmb')) {
            Schema::table('pmb_periods', function (Blueprint $table) {
                $table->unsignedSmallInteger('tahun_pmb')->nullable()->after('nama_gelombang');
            });

            foreach (DB::table('pmb_periods')->get(['id', 'tanggal_tutup', 'tanggal_buka']) as $p) {
                $tanggal = $p->tanggal_tutup ?: $p->tanggal_buka;
                DB::table('pmb_periods')->where('id', $p->id)->update([
                    'tahun_pmb' => $tanggal ? (int) substr((string) $tanggal, 0, 4) : (int) date('Y'),
                ]);
            }
        }

        if (!Schema::hasTable('pmb_kepanitiaans')) {
            Schema::create('pmb_kepanitiaans', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('tahun_pmb');
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('jabatan', 100)->default('Anggota');
                $table->foreignId('ditunjuk_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('ditutup_pada')->nullable();
                $table->timestamps();
                $table->unique(['tahun_pmb', 'user_id'], 'uniq_panitia_tahun_user');
                $table->index(['user_id', 'ditutup_pada'], 'idx_panitia_user_aktif');
            });
        }

        Schema::table('camabas', function (Blueprint $table) {
            if (!Schema::hasColumn('camabas', 'catatan_seleksi')) {
                $table->text('catatan_seleksi')->nullable()->after('status_pendaftaran');
            }
            if (!Schema::hasColumn('camabas', 'diputuskan_oleh')) {
                $table->foreignId('diputuskan_oleh')->nullable()->after('catatan_seleksi')->constrained('users')->nullOnDelete();
                $table->timestamp('diputuskan_pada')->nullable()->after('diputuskan_oleh');
            }
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayarans', 'diverifikasi_oleh')) {
                $table->foreignId('diverifikasi_oleh')->nullable()->after('status')->constrained('users')->nullOnDelete();
                $table->timestamp('diverifikasi_pada')->nullable()->after('diverifikasi_oleh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            if (Schema::hasColumn('pembayarans', 'diverifikasi_oleh')) {
                $table->dropForeign(['diverifikasi_oleh']);
                $table->dropColumn(['diverifikasi_oleh', 'diverifikasi_pada']);
            }
        });
        Schema::table('camabas', function (Blueprint $table) {
            if (Schema::hasColumn('camabas', 'diputuskan_oleh')) {
                $table->dropForeign(['diputuskan_oleh']);
                $table->dropColumn(['diputuskan_oleh', 'diputuskan_pada']);
            }
            if (Schema::hasColumn('camabas', 'catatan_seleksi')) {
                $table->dropColumn('catatan_seleksi');
            }
        });
        Schema::dropIfExists('pmb_kepanitiaans');
        if (Schema::hasColumn('pmb_periods', 'tahun_pmb')) {
            Schema::table('pmb_periods', fn (Blueprint $t) => $t->dropColumn('tahun_pmb'));
        }
    }
};
