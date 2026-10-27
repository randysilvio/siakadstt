<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pengaturan;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\Jadwal;

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

        $pdf = Pdf::loadView('khs.pdf', compact('mahasiswa', 'krsSelected', 'tahunSelected', 'ipsData'));
        
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

        $semuaKrsSelesai = $mahasiswa->mataKuliahs()
            ->wherePivotNotNull('nilai')
            ->get();

        $bobot = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'E' => 0];
        $matkulTerbaik = [];

        foreach ($semuaKrsSelesai as $mk) {
            $nilaiSekarang = $mk->pivot->nilai;
            $bobotSekarang = $bobot[$nilaiSekarang] ?? 0;

            if (isset($matkulTerbaik[$mk->id])) {
                $nilaiTersimpan = $matkulTerbaik[$mk->id]->pivot->nilai;
                $bobotTersimpan = $bobot[$nilaiTersimpan] ?? 0;

                if ($bobotSekarang > $bobotTersimpan) {
                    $matkulTerbaik[$mk->id] = $mk;
                }
            } else {
                $matkulTerbaik[$mk->id] = $mk;
            }
        }

        $krsSelesaiUnik = collect(array_values($matkulTerbaik));
        $krs_per_semester = $krsSelesaiUnik->groupBy('semester');
        $ipk = $mahasiswa->hitungIpk();
        $total_sks = $krsSelesaiUnik->sum('sks');

        $pdf = Pdf::loadView('transkrip.pdf', compact('mahasiswa', 'krs_per_semester', 'total_sks', 'ipk'));
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

        $pdf = Pdf::loadView('krs.pdf', compact('mahasiswa', 'krs', 'rektor', 'tahunAkademik'));
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

        $krsdiambil = $mahasiswa->mataKuliahs()
            ->wherePivot('tahun_akademik_id', $tahunAkademik->id)
            ->get();

        $mataKuliahIds = $krsdiambil->pluck('id')->toArray();
        $kelasMap = [];
        foreach ($krsdiambil as $mk) {
            $kelasMap[$mk->id] = strtoupper($mk->pivot->nama_kelas ?? 'A');
        }

        $jadwals = collect();
        if (!empty($mataKuliahIds)) {
            $jadwals = Jadwal::whereIn('mata_kuliah_id', $mataKuliahIds)
                ->with(['mataKuliah', 'mataKuliah.dosen'])
                ->get()
                ->filter(function ($j) use ($kelasMap) {
                    $klsMhs = $kelasMap[$j->mata_kuliah_id] ?? 'A';
                    return strtoupper($j->nama_kelas ?? 'A') === $klsMhs;
                })
                ->sortBy(function ($jadwal) {
                    // Urut per hari lalu per jam mulai
                    $hariOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
                    return sprintf('%02d-%s', $hariOrder[$jadwal->hari] ?? 99, $jadwal->jam_mulai);
                });
        }

        $pdf = Pdf::loadView('mahasiswa.cetak_jadwal', compact('mahasiswa', 'jadwals', 'tahunAkademik'));
        return $pdf->stream('Jadwal_Kuliah_' . $mahasiswa->nim . '.pdf');
    }
}