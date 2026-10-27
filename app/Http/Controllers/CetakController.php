<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pengaturan;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\Jadwal;
use App\Models\DokumenTerbit;

class CetakController extends Controller
{
    public function cetakKhs(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa;

        if ($mahasiswa->status_evaluasi === 'belum_isi') {
            return redirect()->route('evaluasi.index')
                ->with('error', 'PERHATIAN: Anda wajib menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM) semester ini sebelum dapat mencetak KHS.');
        }

        $selectedTaId = $request->input('tahun_akademik_id');
        if (!$selectedTaId) {
            return redirect()->back()->with('error', 'Gagal mencetak. Silakan pilih Tahun Akademik terlebih dahulu.');
        }

        $tahunSelected = TahunAkademik::findOrFail($selectedTaId);
        
        $krsSelected = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $selectedTaId)
            ->withPivot('nilai', 'nama_kelas')
            ->get();
            
        $ipsData = $mahasiswa->hitungIps($selectedTaId);

        $tokenDokumen = DokumenTerbit::terbitkan('KHS', $mahasiswa, [
            'semester'  => $tahunSelected->nama_semester,
            'ips'       => number_format((float) $ipsData['ips'], 2, '.', ''),
            'total_sks' => (string) $ipsData['total_sks'],
            'nilai'     => $krsSelected->map(fn ($mk) => [$mk->kode_mk, $mk->nama_mk, (string) $mk->sks, (string) ($mk->pivot->nilai ?? '-')])->values()->all(),
        ])->kode;

        $pdf = Pdf::loadView('khs.pdf', compact('mahasiswa', 'krsSelected', 'tahunSelected', 'ipsData', 'tokenDokumen'));
        
        $namaFile = 'KHS_' . $mahasiswa->nim . '_' . str_replace('/', '-', $tahunSelected->tahun) . '_' . strtoupper($tahunSelected->semester) . '.pdf';
        
        if ($request->has('download') && $request->input('download') == 1) {
            return $pdf->download($namaFile);
        }
        
        return $pdf->stream($namaFile);
    }

    public function cetakTranskrip()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa;

        if ($mahasiswa->status_mahasiswa === 'Lulus' && !$mahasiswa->sudah_isi_tracer) {
            return redirect()->route('tracer.index')
                ->with('error', 'Selamat atas kelulusan Anda! Mohon lengkapi kuesioner Tracer Study Alumni terlebih dahulu sebelum mencetak Transkrip Nilai Akhir.');
        }

        if ($mahasiswa->status_mahasiswa !== 'Lulus' && $mahasiswa->status_evaluasi === 'belum_isi') {
            return redirect()->route('evaluasi.index')
                ->with('error', 'PERHATIAN: Anda wajib menyelesaikan pengisian Kuesioner Evaluasi Dosen (EDOM) semester ini sebelum dapat mencetak Transkrip Nilai.');
        }

        [
            'matkul'           => $krsSelesaiUnik,
            'krs_per_semester' => $krs_per_semester,
            'total_sks'        => $total_sks,
            'ipk'              => $ipk,
        ] = app(\App\Services\RekapNilaiService::class)->transkrip($mahasiswa);

        $tokenDokumen = DokumenTerbit::terbitkan('TRANSKRIP', $mahasiswa, [
            'ipk'       => number_format((float) $ipk, 2, '.', ''),
            'total_sks' => (string) $total_sks,
            'nilai'     => $krsSelesaiUnik->sortBy('kode_mk')->map(fn ($mk) => [$mk->kode_mk, $mk->nama_mk, (string) $mk->sks, (string) $mk->pivot->nilai])->values()->all(),
        ])->kode;

        $pdf = Pdf::loadView('transkrip.pdf', compact('mahasiswa', 'krs_per_semester', 'total_sks', 'ipk', 'tokenDokumen'));
        return $pdf->download('Transkrip_' . $mahasiswa->nim . '.pdf');
    }

    public function cetakKrs()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa->load('dosenWali.user', 'programStudi.kaprodi.user');
        
        $tahunAkademik = TahunAkademik::where('is_active', true)->firstOrFail();

        // KRS resmi (ber-segel validasi) hanya dapat dicetak setelah disetujui Dosen Wali / Kaprodi
        if (!$mahasiswa->krsDisetujuiPada($tahunAkademik->id)) {
            return redirect()->route('krs.index')->with('error', 'KRS final hanya dapat dicetak setelah disetujui oleh Dosen Wali / Kaprodi. Status KRS Anda saat ini: ' . strtoupper($mahasiswa->statusKrsPada($tahunAkademik->id)) . '.');
        }

        $krs = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $tahunAkademik->id)
            ->get();

        $rektor = Pengaturan::where('key', 'nama_rektor')->first();

        $tokenDokumen = DokumenTerbit::terbitkan('KRS', $mahasiswa, [
            'semester' => $tahunAkademik->nama_semester,
            'status'   => 'Disetujui',
            'mata_kuliah' => $krs->map(fn ($mk) => [$mk->kode_mk, $mk->nama_mk, (string) $mk->sks, (string) ($mk->pivot->nama_kelas ?? 'A')])->values()->all(),
        ])->kode;

        $pdf = Pdf::loadView('krs.pdf', compact('mahasiswa', 'krs', 'rektor', 'tahunAkademik', 'tokenDokumen'));
        return $pdf->download('KRS_' . $mahasiswa->nim . '_' . str_replace('/', '-', $tahunAkademik->tahun) . '.pdf');
    }

    public function cetakJadwal()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->mahasiswa) {
            abort(403, 'Anda tidak memiliki data mahasiswa.');
        }
        $mahasiswa = $user->mahasiswa->load('programStudi');
        
        $tahunAkademik = TahunAkademik::where('is_active', true)->firstOrFail();

        $jadwals = app(\App\Services\JadwalService::class)->jadwalMahasiswa($mahasiswa, $tahunAkademik->id, ['mataKuliah', 'mataKuliah.dosen']);

        $pdf = Pdf::loadView('mahasiswa.cetak_jadwal', compact('mahasiswa', 'jadwals', 'tahunAkademik'));
        return $pdf->stream('Jadwal_Kuliah_' . $mahasiswa->nim . '.pdf');
    }
}