<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\DosenTridharma;
use App\Models\TracerStudy;
use App\Models\Camaba;
use App\Models\TahunAkademik;
use App\Models\EvaluasiSesi;
use App\Models\EvaluasiJawaban;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MutuReportController extends Controller
{
    public function index()
    {
        $prodis = ProgramStudi::orderBy('nama_prodi')->get();
        $tahunTersedia = Mahasiswa::select('tahun_masuk')
                            ->whereNotNull('tahun_masuk')
                            ->distinct()
                            ->orderBy('tahun_masuk', 'desc')
                            ->pluck('tahun_masuk');

        if ($tahunTersedia->isEmpty()) {
            $tahunTersedia = collect([date('Y')]);
        }
        
        return view('penjaminan_mutu.laporan.index', compact('prodis', 'tahunTersedia'));
    }

    /**
     * Cetak Ringkasan Kinerja (Laporan Eksekutif SPMI Terintegrasi Tracer Study & Tridharma)
     */
    public function cetakRingkasan()
    {
        $jumlahMahasiswaAktif = Mahasiswa::where('status_mahasiswa', 'Aktif')->count();
        $jumlahDosen = Dosen::count();
        $rasio = $jumlahDosen > 0 ? '1 : ' . round($jumlahMahasiswaAktif / $jumlahDosen, 1) : 'N/A';

        $mahasiswas = Mahasiswa::isiRekapNilai(Mahasiswa::where('status_mahasiswa', 'Aktif')->get());
        $totalIpk = 0;
        $count = 0;
        foreach ($mahasiswas as $mhs) {
            $ipkMhs = $mhs->hitungIpk();
            if ($ipkMhs > 0) {
                $totalIpk += $ipkMhs;
                $count++;
            }
        }
        $rataIPK = $count > 0 ? round($totalIpk / $count, 2) : 0.00;

        $trenData = Mahasiswa::select('tahun_masuk', DB::raw('count(*) as total'))
            ->where('status_mahasiswa', 'Aktif')
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'desc')
            ->limit(5)
            ->get();

        $sesiEdomAktif = EvaluasiSesi::where('is_active', true)->first();
        $hasilEdom = collect();
        if ($sesiEdomAktif) {
            $hasilEdom = EvaluasiJawaban::where('evaluasi_sesi_id', $sesiEdomAktif->id)
                ->join('dosens', 'evaluasi_jawaban.dosen_id', '=', 'dosens.id')
                ->select('dosens.nama_lengkap', DB::raw('AVG(jawaban_skala) as rata_rata_skor'))
                ->groupBy('dosens.id', 'dosens.nama_lengkap')
                ->orderBy('rata_rata_skor', 'desc')
                ->limit(10)
                ->get();
        }

        $dataLulusan = Mahasiswa::isiRekapNilai(Mahasiswa::where('status_mahasiswa', 'Lulus')->get())
            ->groupBy(function ($item) {
                return $item->tanggal_lulus ? Carbon::parse($item->tanggal_lulus)->format('Y') : $item->updated_at->format('Y'); 
            })
            ->map(function ($mahasiswas, $tahunLulus) {
                $jumlahLulusan = $mahasiswas->count();
                $totalIpk = 0;
                $totalMasaStudi = 0;

                foreach ($mahasiswas as $mhs) {
                    $totalIpk += $mhs->hitungIpk();
                    $masaStudi = (int) $tahunLulus - (int) $mhs->tahun_masuk;
                    $totalMasaStudi += max($masaStudi, 1); 
                }

                return (object) [
                    'tahun_lulus' => $tahunLulus,
                    'jumlah' => $jumlahLulusan,
                    'rata_ipk' => round($totalIpk / $jumlahLulusan, 2),
                    'rata_masa_studi' => round($totalMasaStudi / $jumlahLulusan, 1)
                ];
            })
            ->sortByDesc('tahun_lulus')
            ->values();

        // Ringkasan Tracer Study Alumni & Produktivitas Tridharma Dosen
        $tracers = TracerStudy::all();
        $ringkasanTracer = [
            'responden'    => $tracers->count(),
            'waktu_tunggu' => $tracers->count() > 0 ? round($tracers->avg('waktu_tunggu_bulan'), 1) : 0,
            'kesesuaian'   => $tracers->count() > 0 ? round(($tracers->whereIn('kesesuaian_bidang', ['Sangat Sesuai', 'Sesuai'])->count() / $tracers->count()) * 100, 1) : 0,
        ];

        $ringkasanTridharma = [
            'total_penelitian' => DosenTridharma::where('unsur', 'penelitian')->count(),
            'total_pengabdian' => DosenTridharma::where('unsur', 'pengabdian')->count(),
            'total_penunjang'  => DosenTridharma::where('unsur', 'penunjang')->count(),
        ];

        $pdf = Pdf::loadView('penjaminan_mutu.laporan.pdf_ringkasan', compact(
            'jumlahMahasiswaAktif', 'jumlahDosen', 'rasio', 'rataIPK', 
            'trenData', 'hasilEdom', 'sesiEdomAktif', 'dataLulusan',
            'ringkasanTracer', 'ringkasanTridharma'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Ringkasan_Kinerja_Mutu.pdf');
    }

    /**
     * Cetak Laporan Beban Kerja Dosen (Menyertakan Kalkulasi BKD Tridharma)
     */
    public function cetakBebanDosen()
    {
        $dosens = Dosen::with(['mataKuliahs', 'tridharmas'])->orderBy('nama_lengkap')->get();

        $laporanDosen = $dosens->map(function ($dosen) {
            // Sama dengan kalkulator BKD: semester aktif, SKS × jumlah kelas paralel, team teaching penuh
            $bkd = $dosen->hitungRingkasanBkd();
            $jumlahKelas = $bkd['jumlah_kelas_ajar'];
            $totalSks = $bkd['sks_pengajaran'];

            return [
                'nidn'          => $dosen->nidn,
                'nama'          => $dosen->nama_lengkap,
                'jabatan'       => $dosen->jabatan_akademik ?? '-',
                'jumlah_mk'     => $jumlahKelas,
                'total_sks'     => $totalSks,
                'total_sks_bkd' => $bkd['total_sks_bkd'],
                'status'        => $bkd['status_bkd'],
                'mata_kuliahs'  => $dosen->mataKuliahs,
            ];
        });

        $pdf = Pdf::loadView('penjaminan_mutu.laporan.pdf_beban_dosen', compact('laporanDosen'))
                  ->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Beban_Kerja_Dosen_' . date('Ymd') . '.pdf');
    }

    /**
     * [FITUR BARU FASE 8]: Cetak Laporan Produktivitas Penelitian & PkM Dosen (Tridharma)
     */
    public function cetakTridharmaDosen()
    {
        $tridharmas = DosenTridharma::with('dosen.programStudi')
            ->orderBy('tahun', 'desc')
            ->orderBy('unsur')
            ->get();

        $pdf = Pdf::loadView('penjaminan_mutu.laporan.pdf_tridharma_dosen', compact('tridharmas'))
                  ->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Produktivitas_Tridharma_Dosen_' . date('Ymd') . '.pdf');
    }

    public function cetakRps(Request $request)
    {
        $request->validate([
            'lingkup' => 'required',
            'program_studi_id' => 'required_if:lingkup,prodi',
        ]);

        $query = MataKuliah::with(['dosen', 'kurikulum.programStudi']);

        if ($request->lingkup == 'prodi') {
            $prodi = ProgramStudi::findOrFail($request->program_studi_id);
            $query->whereHas('kurikulum', function ($q) use ($request) {
                $q->where('program_studi_id', $request->program_studi_id);
            });
            $judulLingkup = "PROGRAM STUDI " . strtoupper($prodi->nama_prodi);
        } else {
            $judulLingkup = "SELURUH PROGRAM STUDI (INSTITUSI)";
        }

        $dataMataKuliah = $query->orderBy('semester')->get();
        $totalMK = $dataMataKuliah->count();
        $sudahUpload = $dataMataKuliah->whereNotNull('file_rps')->count();
        $persentase = $totalMK > 0 ? round(($sudahUpload / $totalMK) * 100, 1) : 0;

        $pdf = Pdf::loadView('penjaminan_mutu.laporan.pdf_rps', compact('dataMataKuliah', 'judulLingkup', 'totalMK', 'sudahUpload', 'persentase'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Ketersediaan_RPS_' . date('Ymd') . '.pdf');
    }

    public function cetakDistribusiNilai(Request $request)
    {
        $request->validate([
            'lingkup' => 'nullable|string',
            'program_studi_id' => 'required_if:lingkup,prodi',
        ]);

        $lingkup = $request->input('lingkup', 'institusi');
        $tahunAkademikAktif = TahunAkademik::where('is_active', true)->first();

        $query = MataKuliah::with(['dosen', 'kurikulum.programStudi', 'mahasiswas']);

        if ($lingkup === 'prodi' && $request->filled('program_studi_id')) {
            $prodi = ProgramStudi::findOrFail($request->program_studi_id);
            $query->whereHas('kurikulum', function ($q) use ($request) {
                $q->where('program_studi_id', $request->program_studi_id);
            });
            $judulLingkup = "PROGRAM STUDI " . strtoupper($prodi->nama_prodi);
        } else {
            $judulLingkup = "SELURUH PROGRAM STUDI (INSTITUSI)";
        }

        $mataKuliahs = $query->orderBy('semester')->orderBy('kode_mk')->get();

        $dataDistribusi = $mataKuliahs->map(function ($mk) {
            $pesertaDinilai = $mk->mahasiswas->filter(fn($mhs) => !is_null($mhs->pivot->nilai));
            $total = $pesertaDinilai->count();

            $countA = $pesertaDinilai->filter(fn($m) => strtoupper($m->pivot->nilai) === 'A')->count();
            $countB = $pesertaDinilai->filter(fn($m) => strtoupper($m->pivot->nilai) === 'B')->count();
            $countC = $pesertaDinilai->filter(fn($m) => strtoupper($m->pivot->nilai) === 'C')->count();
            $countD = $pesertaDinilai->filter(fn($m) => strtoupper($m->pivot->nilai) === 'D')->count();
            $countE = $pesertaDinilai->filter(fn($m) => strtoupper($m->pivot->nilai) === 'E')->count();

            return [
                'kode_mk'   => $mk->kode_mk,
                'nama_mk'   => $mk->nama_mk,
                'semester'  => $mk->semester,
                'sks'       => $mk->sks,
                'dosen'     => $mk->dosen->nama_lengkap ?? '-',
                'total'     => $total,
                'A'         => $countA,
                'B'         => $countB,
                'C'         => $countC,
                'D'         => $countD,
                'E'         => $countE,
                'lulus_pct' => $total > 0 ? round((($countA + $countB + $countC + $countD) / $total) * 100, 1) : 0,
            ];
        });

        $pdf = Pdf::loadView('penjaminan_mutu.laporan.pdf_distribusi_nilai', compact(
            'dataDistribusi',
            'judulLingkup',
            'tahunAkademikAktif'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Distribusi_Nilai_' . date('Ymd') . '.pdf');
    }

    public function cetakMahasiswa(Request $request)
    {
        $request->validate([
            'lingkup' => 'required',
            'program_studi_id' => 'required_if:lingkup,prodi',
            'tahun_saat_ini' => 'required|numeric',
        ]);

        $ts = (int) $request->tahun_saat_ini;
        $years = [$ts - 2, $ts - 1, $ts];
        $laporan = [];

        foreach ($years as $tahun) {
            $queryMasuk = Mahasiswa::where('tahun_masuk', $tahun);
            if ($request->lingkup == 'prodi') {
                $queryMasuk->where('program_studi_id', $request->program_studi_id);
            }
            $maba = $queryMasuk->count();

            $queryAktif = Mahasiswa::where('tahun_masuk', $tahun)->where('status_mahasiswa', 'Aktif');
            if ($request->lingkup == 'prodi') {
                $queryAktif->where('program_studi_id', $request->program_studi_id);
            }
            $aktif = $queryAktif->count();

            $queryCamaba = Camaba::whereYear('created_at', $tahun);
            if ($request->lingkup == 'prodi') {
                $queryCamaba->where('pilihan_prodi_1_id', $request->program_studi_id);
            }
            $pendaftarCamaba = (clone $queryCamaba)->count();
            $lulusCamaba = (clone $queryCamaba)->where('status_pendaftaran', 'lulus')->count();

            $calonPendaftarRiil = max($pendaftarCamaba, $maba);
            $lulusSeleksiRiil = max($lulusCamaba, $maba);

            $laporan[$tahun] = [
                'daya_tampung'     => 30,
                'calon_pendaftar'  => $calonPendaftarRiil,
                'lulus_seleksi'    => $lulusSeleksiRiil,
                'mahasiswa_baru'   => $maba,
                'mahasiswa_aktif'  => $aktif,
            ];
        }

        if ($request->lingkup == 'prodi') {
            $prodi = ProgramStudi::findOrFail($request->program_studi_id);
            $judulLingkup = "PROGRAM STUDI " . strtoupper($prodi->nama_prodi);
        } else {
            $judulLingkup = "TINGKAT INSTITUSI (STT GPI PAPUA)";
        }

        $pdf = Pdf::loadView('penjaminan_mutu.laporan.pdf_mahasiswa', compact('laporan', 'judulLingkup', 'years'))
                  ->setPaper('a4', 'landscape'); 

        return $pdf->stream('Laporan_Student_Body_' . date('Ymd') . '.pdf');
    }
}