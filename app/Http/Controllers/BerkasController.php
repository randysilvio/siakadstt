<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPegawai;
use App\Models\BimbinganLog;
use App\Models\Pembayaran;
use App\Models\PmbDocument;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\VerumPengumpulan;
use App\Support\BerkasPrivat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Akses berkas sensitif melalui pemeriksaan hak akses (bukan URL publik /storage).
 */
class BerkasController extends Controller
{
    public function unduh(string $jenis, int $id, string $sisi = 'masuk')
    {
        /** @var User $user */
        $user = Auth::user();

        [$path, $boleh] = match ($jenis) {
            'bukti-bayar'    => $this->buktiBayar($user, $id),
            'dokumen-pmb'    => $this->dokumenPmb($user, $id),
            'draf-bimbingan' => $this->drafBimbingan($user, $id),
            'jawaban-tugas'  => $this->jawabanTugas($user, $id),
            'foto-absensi'   => $this->fotoAbsensi($user, $id, $sisi),
            default          => abort(404),
        };

        if (!$boleh) {
            abort(403, 'Anda tidak berhak membuka berkas ini.');
        }

        $absolut = BerkasPrivat::pathAbsolut($path);
        if (!$absolut) {
            abort(404, 'Berkas tidak ditemukan di server.');
        }

        return response()->file($absolut, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function buktiBayar(User $user, int $id): array
    {
        $p = Pembayaran::findOrFail($id);
        $adminPmb = $p->jenis_pembayaran === 'formulir_pmb' && $this->bolehPmb($user, \App\Models\Camaba::with('period')->where('user_id', $p->user_id)->first());
        $boleh = $user->hasRole('keuangan')
            || $adminPmb
            || (int) $p->user_id === (int) $user->id
            || ($user->mahasiswa && (int) $p->mahasiswa_id === (int) $user->mahasiswa->id);
        return [$p->bukti_bayar, $boleh];
    }

    private function dokumenPmb(User $user, int $id): array
    {
        $doc = PmbDocument::with('camaba.period')->findOrFail($id);
        $boleh = $this->bolehPmb($user, $doc->camaba) || ($doc->camaba && (int) $doc->camaba->user_id === (int) $user->id);
        return [$doc->path_file, $boleh];
    }

    private function drafBimbingan(User $user, int $id): array
    {
        $log = BimbinganLog::with('bimbingan.mahasiswa')->findOrFail($id);
        $b = $log->bimbingan;
        $boleh = $user->hasRole('admin')
            || ($user->mahasiswa && (int) $b->mahasiswa_id === (int) $user->mahasiswa->id)
            || ($user->dosen && (
                in_array((int) $user->dosen->id, [(int) $b->pembimbing_1_id, (int) $b->pembimbing_2_id], true)
                || ProgramStudi::where('id', optional($b->mahasiswa)->program_studi_id)->where('kaprodi_dosen_id', $user->dosen->id)->exists()
            ));
        return [$log->file_draf, $boleh];
    }

    private function jawabanTugas(User $user, int $id): array
    {
        $kumpul = VerumPengumpulan::with('tugas.kelas')->findOrFail($id);
        $kelas = optional($kumpul->tugas)->kelas;
        $boleh = ($user->mahasiswa && (int) $kumpul->mahasiswa_id === (int) $user->mahasiswa->id)
            || ($kelas && $user->dosen && (
                (int) $kelas->dosen_id === (int) $user->dosen->id
                || DB::table('dosen_mata_kuliah')->where('mata_kuliah_id', $kelas->mata_kuliah_id)->where('dosen_id', $user->dosen->id)->exists()
            ));
        return [$kumpul->file_path, $boleh];
    }

    private function fotoAbsensi(User $user, int $id, string $sisi): array
    {
        $absen = AbsensiPegawai::findOrFail($id);
        $boleh = $user->hasRole('admin') || (int) $absen->user_id === (int) $user->id;
        return [$sisi === 'pulang' ? $absen->foto_check_out : $absen->foto_check_in, $boleh];
    }

    /** Admin, atau Panitia PMB yang menangani tahun PMB pendaftar tersebut. */
    private function bolehPmb(User $user, ?\App\Models\Camaba $camaba): bool
    {
        if (!$camaba) {
            return false;
        }
        if (!$user->hasRole('admin') && !$user->isPanitiaPmb()) {
            return false;
        }
        return app(\App\Services\PmbService::class)->bolehAksesCamaba($user, $camaba);
    }
}
